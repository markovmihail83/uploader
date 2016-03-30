Пользовательский адаптер файловой системы.
==========================================

###### Для создание пользовательского адаптера, реализуйте интерфейс [<sub>`Atom\Uploader\Filesystem\IFilesystemAdapter`.</sub>](../../src/Filesystem/IFilesystemAdapter.php)

Адаптер должен иметь следующие методы:
```php
/**
 * Должен записать поток в файл используя префикс и путь(относительно префиксу).
 *
 * @param string $prefix
 * @param string $path
 * @param resource $resource
 *
 * @throws \Exception если по каким то причинам не сможет записать в файл то должно бросит исключение.
 */
public function writeStream($prefix, $path, $resource);

/**
 * Должен попробовать удалит файл используя префикс и путь.
 *
 * @param string $prefix
 * @param string $path
 *
 * @return bool если файл успешно удален то должен вернут true иначе false.
 */
public function delete($prefix, $path);

/**
 * Должен попробовать найти файл используя префикс и путь.
 *
 * @param string $prefix
 * @param string $path
 *
 * @return \SplFileInfo|null если нашел то должен вернут экземпляр \SplFileInfo иначе null.
 */
public function resolveFileInfo($prefix, $path);
```