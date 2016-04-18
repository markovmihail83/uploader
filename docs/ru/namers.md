<a name="top"></a>Неймеры.
===

** Готовые неймеры: **
- [Неймер уникальных имен.](#unique_id)
- [Неймер базовых имен.](#basename)

** Создать свое **

Для создание пользовательского неймера, реализуйте интерфейс [`Atom\Uploader\Naming\INamer`][INamer].

<a name="unique_id"/></a>Неймер уникальных имен.
---

```php
    $uniqueNamer = new Atom\Uploader\Naming\UniqueNamer();
```

<a name="basename"></a>Неймер базовых имен.
---

```php
    $basenameNamer = new Atom\Uploader\Naming\BasenameNamer();
```

[INamer]: ../../src/Naming/INamer.php