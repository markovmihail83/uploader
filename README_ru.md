Эта гибкая PHP библиотека которая отвечает за сохранность файлов.
=================================================================

[![Join the chat at https://gitter.im/atom-azimov/uploader](https://badges.gitter.im/atom-azimov/uploader.svg)](https://gitter.im/atom-azimov/uploader?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![License](https://poser.pugx.org/atom-azimov/uploader/license)](https://github.com/atom-azimov/uploader/blob/master/LICENSE)
[![Build Status](https://travis-ci.org/atom-azimov/uploader.svg?branch=master)](https://travis-ci.org/atom-azimov/uploader)
[![Latest Stable Version](https://poser.pugx.org/atom-azimov/uploader/v/stable)](https://packagist.org/packages/atom-azimov/uploader)
[![Latest Unstable Version](https://poser.pugx.org/atom-azimov/uploader/v/unstable)](https://packagist.org/packages/atom-azimov/uploader)
[![Dependency Status](https://www.versioneye.com/user/projects/56c6762318b271002c69b141/badge.svg?style=flat)](https://www.versioneye.com/user/projects/56c6762318b271002c69b141)
[![Total Downloads](https://poser.pugx.org/atom-azimov/uploader/downloads)](https://packagist.org/packages/atom-azimov/uploader)
[![Coverage Status](https://coveralls.io/repos/github/atom-azimov/uploader/badge.svg?branch=master)](https://coveralls.io/github/atom-azimov/uploader?branch=master)
[![Code Climate](https://codeclimate.com/github/atom-azimov/uploader/badges/gpa.svg)](https://codeclimate.com/github/atom-azimov/uploader)
[![Issue Count](https://codeclimate.com/github/atom-azimov/uploader/badges/issue_count.svg)](https://codeclimate.com/github/atom-azimov/uploader)

---

Возможности:
------------

- Сохраняет прикрепленных файлов на файловой системе или на облаке.
- Внедряет информацию о файле при загрузки данных.
- Внедряет URI при загрузки данных.
- Удаляет неиспользуемые файлы при необходимости(после удалении или обновлении данных).
- Отправляет событие при каждом действии(в событиях можно остановит действие).

Как пользоваться?
-----------------

#### Используйте интеграцию для своего Фреймворка из следующего списка:

* [Symfony](https://github.com/atom-azimov/uploader-bundle)

##### или интегрируйте со своим Фреймворком используя [эту инструкцию](docs/ru/integration.md).