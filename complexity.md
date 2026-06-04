# Проблемы сложности в проекте «Космический бой» и способы их решения

В этом документе описаны конкретные проблемы сложности, с которыми сталкивается проект, и архитектурные решения, которые были применены для их устранения.

---

## 1. Обнаружение столкновений

### Проблема реализации "в лоб" за 0(nˆ2)

Самый очевидный способ проверить, попала ли ракета в корабль сравнить каждый движущийся объект с каждым другим:

```php
foreach ($rockets as $rocket) {
    foreach ($ships as $ship) {
        if ($this->intersects($rocket->getPosition(), $ship->getPosition())) {
            $ship->destroy();
            $rocket->deactivate();
        }
    }
}
```

При 6 кораблях и 10 ракетах - 60 проверок за тик. При 100 объектах - 10 000, сильно нагрузит прод даже при относительно небольших сессиях.

### Решение: NeighborhoodSystem O(1)

Игровое поле разбито на ячейки фиксированного размера. Каждый объект хранится в ячейке по ключу `x, y`. При движении ракеты нужно проверить коллизию только с объектами в той же ячейке:

```php
$this->neighborhood->updateObjectNeighborhood($rocket->getGameObject());
$neighbors = $this->neighborhood->getObjectsInSameNeighborhood(
    $rocket->getGameObject()
);
foreach ($neighbors as $neighbor) {
    $ship = $ships[$neighbor->getId()] ?? null;
    if ($ship !== null && $ship->isAlive()) {
        $ship->destroy();
        $rocket->deactivate();
    }
}
```

Поиск по хеш-таблице - **O(1)**. Сложность проверки коллизий за тик равна **O(n)** по числу объектов (перебираем всех), но каждая проверка - **O(1)** вместо O(n).

---

## 2. Управление зависимостями: глобальный контекст -> IoC scope

### Проблема без IoC

Без IoC-контейнера зависимости между компонентами игры пришлось бы передавать вручную через конструкторы или хранить в глобальных переменных:

```php
class GameTickCommand {
    public function __construct(
        private array &$ships,
        private array &$rockets,
        private NeighborhoodSystem $neighborhood,
        private Fleet $fleetA,
        private Fleet $fleetB,
        private WinConditionCheckerInterface $winChecker,
        private string $gameId,
    ) {}
}
```

Так же при нескольких одновременных игровых сессиях без изоляции контекста объекты одной игры начинают видеть состояние другой.

### Решение: изолированный IoC-scope на каждую игровую сессию

Для каждой игры создаётся отдельный scope в IoC-контейнере. Все зависимости регистрируются внутри этого scope:

```php
IoC::resolve('Scopes.New', $gameId)->execute();
IoC::resolve('Scopes.Current', $gameId)->execute();
IoC::resolve('IoC.Register', 'Game.Actions.shoot',
    static function (SpaceShip $ship) use (&$rockets, $neighborhood) {
        return new ShootCommand($ship, $rockets, $neighborhood);
    }
)->execute();
```

**Что даёт изоляция:**
- 10 одновременных партий - 10 независимых scope, ни один объект не виден в чужом контексте;
- Добавление новой команды через одну строку `IoC.Register`, без изменения других классов;

---

## 3. Добавление новых команд: жёсткая связанность -> Open/Closed через IoC

### Без IoC-регистрации каждое новое действие потребовало бы правки через if (что нарушает OCP):

```php
class OrderInterpreterCommand {
    public function execute(): void {
        $type = $this->order->getType();
        if ($type === 'move') {
            (new MoveCommand($ship))->execute();
        } elseif ($type === 'rotate') {
            (new RotateCommand($ship))->execute();
        }
        ....
    }
}
```

Добавление любой новой команды через редакитрование уже работающего класса и рискуем сломать существующие команды.

### Решение: регистрация через IoC

```php
IoC::resolve('IoC.Register', 'Game.Actions.move',
    static fn(SpaceShip $ship) => new MoveCommand($ship)
)->execute();

$command = IoC::resolve("Game.Actions.{$order->getType()}", $ship);
$command->execute();
```

Чтобы добавить новое действие достаточно одной строки `IoC.Register` в `GameInitializer`.

---

## 4. Составные команды: копипаст -> DSL

### Проблема без DSL

Команда "двигаться с расходом топлива" - это три последовательных действия: проверить топливо, переместить, списать топливо. Без MacroCommand это либо новый жёстко закодированный класс, либо дублирование. N видов составных команд = N новых классов.:

```php
class MoveWithFuelCommand {
    public function execute(): void {
        (new CheckFuelCommand($this->ship))->execute();
        (new MoveCommand($this->ship))->execute();
        (new BurnFuelCommand($this->ship))->execute();
    }
}
```

### Решение: MacroCommandFactory (DSL)

```php
$registry->define('Ship.MoveWithFuel', ['CheckFuel', 'Move', 'BurnFuel']);

public function build(string $macroName, mixed ...$args): MacroCommand {
    $commands = array_map(
        fn($op) => IoC::resolve($op, ...$args),
        $this->registry->get($macroName)
    );
    return new MacroCommand($commands);
}
```

---

## 5. Проверка победителя через паттерн Strategy

### Без Strategy - условие победы захардкожено в игровом цикле

```php
class GameTickCommand {
    private function checkWin(): ?string {
        $aAlive = count(array_filter($this->ships, fn($s) => $s->getTeam() === 'TEAM_A' && $s->isAlive()));
        $bAlive = count(array_filter($this->ships, fn($s) => $s->getTeam() === 'TEAM_B' && $s->isAlive()));
        if ($aAlive === 0) return 'TEAM_B';
        if ($bAlive === 0) return 'TEAM_A';
        return null;
    }
}
```

Захотели добавить победу по очкам или ничью, то придется менять GameTickCommand каждый раз.

### Решение: Strategy через WinConditionCheckerInterface

```php
interface WinConditionCheckerInterface {
    public function check(Fleet $fleetA, Fleet $fleetB, array $ships): ?string;
}

$winner = $this->winChecker->check($this->fleetA, $this->fleetB, $this->ships);
```

Захотели новый турнир с нечьей - создаём новый класс и регистрируем через DI. `GameTickCommand` не трогаем.

---

## 6. Динамическая генерация адаптеров

### Проблема без AdapterGenerator: lля каждого интерфейса (`MovableInterface`, `RotatableInterface`, `FuelableInterface`) пришлось бы вручную писать адаптер:

```php
class MovableAdapter implements MovableInterface {
    public function getLocation(): Point
    public function getVelocity(): int
    public function getDirection(): int
    public function setLocation(Point $p): void
}

class RotatableAdapter implements RotatableInterface {
    public function getDirection(): int
    public function getAngularVelocity(): int
    public function setDirection(int $direction): void
}
```

### Решение: AdapterGenerator через ПХП рефлексию

`AdapterGenerator` автоматически генерирует класс-адаптер для любого интерфейса: читает его методы через рефлексию, строит код сам. Кажлдый новый интерфейс не провоцирует изменения в существующем коде.
