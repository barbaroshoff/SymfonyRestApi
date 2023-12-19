Управление списком книг API

Это API разработано для управления списком книг с использованием Symfony и FOSRestBundle.
Позволяет выполнять CRUD операции (Create, Read, Update, Delete) для книг.

Установка и запуск

    Установка зависимостей:
    composer install
    
Настройка базы данных:

    Настройте файл .env для соединения с вашей базой данных.
    Выполните миграции для создания необходимых таблиц:
    php bin/console doctrine:migrations:migrate

Загрузка записей в бд:

    php bin/console app:generate-million-books

Запуск сервера:

    php bin/console server:run

Документация проекта:

    http://127.0.0.1:8000/api/doc


