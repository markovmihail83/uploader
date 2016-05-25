AtomUploader
===

PHP библиотека которая обеспечивает сохранение загруженных файлов.

[![Build Status](https://travis-ci.org/atom-azimov/uploader.svg?branch=master)](https://travis-ci.org/atom-azimov/uploader)
[![Join the chat at https://gitter.im/atom-azimov/uploader](https://badges.gitter.im/atom-azimov/uploader.svg)](https://gitter.im/atom-azimov/uploader?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![License](https://poser.pugx.org/atom-azimov/uploader/license)](https://github.com/atom-azimov/uploader/blob/master/LICENSE)
[![Dependency Status](https://www.versioneye.com/user/projects/56c6762318b271002c69b141/badge.svg?style=flat)](https://www.versioneye.com/user/projects/56c6762318b271002c69b141)

[![Latest Stable Version](https://poser.pugx.org/atom-azimov/uploader/v/stable)](https://packagist.org/packages/atom-azimov/uploader)
[![Latest Unstable Version](https://poser.pugx.org/atom-azimov/uploader/v/unstable)](https://packagist.org/packages/atom-azimov/uploader)
[![Total Downloads](https://poser.pugx.org/atom-azimov/uploader/downloads)](https://packagist.org/packages/atom-azimov/uploader)
[![Code Climate](https://codeclimate.com/github/atom-azimov/uploader/badges/gpa.svg)](https://codeclimate.com/github/atom-azimov/uploader)

---

Мотивация
---

Проект создавался с целью облегчить загрузку файлов, используя [встраиваемые объекты doctrine][embeddables].<br />
Но он не зависит от doctrine и его можно использовать с другими хранилищами данных, даже с простыми массивами.

Возможности:
---

- Автоматическое создание имён и сохранение файлов;
- Внедрение файла обратно в объект, когда он будет загружен из хранилища данных, как экземпляр `\SplFileInfo`;
- Внедрение URI в объект, когда он будет загружен из хранилища данных;
- Удаление файла из файловой системы при удалении (или обновлении) объекта из хранилища данных;

Весь функционал настраиваемый.

Как пользоваться?
----

#### Используйте интеграцию для своего Фреймворка:

* [Symfony]

> Если в списке отсутствует интеграция для вашего Фреймворка, то напишите issue.
> А если не хотите ждать и можете самостаятельно интегрировать с вашим фреймворком то прочитайте [инструкцию][integration].

[integration]: docs/ru/integration.md
[symfony]: https://github.com/atom-azimov/uploader-bundle
[embeddables]: http://doctrine-orm.readthedocs.org/projects/doctrine-orm/en/latest/tutorials/embeddables.html
