Тестовый проект для сбора картинок с сайта.

Рекурсивно проходится по всем урлам сайта, и собирает все картинки по атрибуту src из тегов img.
сохраняет в storage/images.

есть возможность добавлять плагины PluginsManager.php
Плагины надо унаследовать от BasePlugin.php
Плагины кастомизируют следующие места:
1) фильтрация урлов для поиска картинок.
2) фильтрация картинок необходимых для закачки.
3) доп обработка картинок

