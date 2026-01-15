# Corporate car reservation

Приложение по резервированию свободных автомобилей с различными видами комфорта для сотрудников с различными должностями.

Название должности - уровень должности

```
    Intern           - 0
    Junior Developer - 1
    Middle Developer - 2
    Senior Developer - 3
    Team Lead        - 3
    Project Manager  - 3
    Department Head  - 4
    Director         - 5
    CEO              - 5
```

Категории комфорта машин:

```
    Economy      - 1
    Comfort      - 2
    Business     - 3
    PremiuLuxury - 5
```

Сопоставление уровня должности и разрешенный уровень комфорта

```mono
    уровень должности 1 = допускаемый уровень комфорта машины 1
    уровень должности 2 = допускаемый уровень комфорта машины 1, 2
    уровень должности 3 = допускаемый уровень комфорта машины 2, 3
    уровень должности 4 = допускаемый уровень комфорта машины 3, 4
    уровень должности 5 = допускаемый уровень комфорта машины 4, 5
```

## Запустить проект

Запустить контейнер с MySQL бд и PhpMyAdmim

`docker compose up -d`

Запуск самого приложения

`php artisan serve`

Создание таблиц

`php artisan migrate`

Наполнение тестовыми данными

`php artisan db:seed`

Создаем токены для всех сотрудников (не водителей)

`php artisan tokens:manage --create`

Или только для 1 сотрудника (CEO) с уровнем должности 5

`php artisan tokens:manage 9 --create`

Сделать API запрос на полчение свободных машин для сотрудника

```sh
curl -X GET \
    'http://localhost:8000/api/available-car?start_date=2026-01-15T13:59:00&model=Bentley&category=5' \
    -H 'Authorization: Bearer {token}' \
    -H 'Accept: application/json'
```

Получить список всех поездок

```sh
curl -X GET 'http://localhost:8000/api/trips'
```
