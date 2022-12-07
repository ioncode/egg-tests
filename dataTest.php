<?php

/*Напиши SQL-запрос
Имеем следующие таблицы:
users — контрагенты
id
name
phone
email
created — дата создания записи
orders — заказы
id
subtotal — сумма всех товарных позиций
created — дата и время поступления заказа (Y-m-d H:i:s)
city_id — город доставки
user_id

Необходимо выбрать одним запросом следующее (следует учесть, что будет включена опция only_full_group_by в MySql):
Имя контрагента
Его телефон
Сумма всех его заказов
Его средний чек
Дата последнего заказа*/

$connection = new mysqli("localhost", "root", "root");

if ($connection->query('DROP DATABASE IF EXISTS EGG')) {
    echo 'DB dropped' . PHP_EOL;
    $query = 'CREATE DATABASE EGG';
    if ($connection->query($query)) {
        echo 'new DB created' . PHP_EOL;
        if ($connection->query('USE EGG')) {
            echo 'Used new schema EGG' . PHP_EOL;
            if ($connection->query('CREATE TABLE users (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(30) NOT NULL,
phone VARCHAR(30) NOT NULL,
email VARCHAR(50) NOT NULL ,
created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)')) {
                echo 'users table created successfully' . PHP_EOL;
                foreach ([
                             ['Andrey', '89091391777', 'ionrussia@gmail.com'],
                             ['Boris', mt_rand(89000000000, 89999999999), 'me@gmail.com']
                         ] as $user) {
                    echo 'creating user from ' . print_r($user, 1);
                    $statement = $connection->prepare('INSERT INTO users(name, phone, email) VALUES (?, ?, ?)');
                    $statement->bind_param('sss', $user[0], $user[1], $user[2]);
                    if ($statement->execute()) {
                        echo 'User successfully created' . PHP_EOL;
                    }
                }
                echo 'Creating orders table...' . PHP_EOL;
                if ($connection->query('CREATE TABLE orders (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
subtotal DECIMAL(10,2) NOT NULL,
created DATETIME DEFAULT NOW(),
city_id INT(6) NOT NULL,
user_id INT(6) UNSIGNED NOT NULL,
INDEX city(city_id), 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE 
)')) {
                    echo 'orders table created successfully' . PHP_EOL;
                    foreach ([
                                 [12.3, 43, 1],
                                 [123, 12, 1],
                                 [500, 99, 1],
                                 [32, 24, 1],
                                 [33.31, 77, 1],
                                 [10000, 77, 2],
                                 [10000, 77, 2],
                             ] as $order) {
                        echo 'creating order from ' . print_r($order, 1);
                        $statement = $connection->prepare('INSERT INTO orders(subtotal, city_id, user_id) VALUES (?, ?, ?)');
                        $statement->bind_param('dii', $order[0], $order[1], $order[2]);
                        if ($statement->execute()) {
                            echo 'Order successfully created' . PHP_EOL;
                        }
                    }
                    echo 'Fetching....' . PHP_EOL;
                    $sqlMode = $connection->query("SHOW VARIABLES LIKE 'sql_mode'")->fetch_row();
                    print_r($sqlMode[1]);
                    $result = $connection->query('select
    name as `Имя контрагента`,
    phone as `телефон`,
    SUM(subtotal) as `Сумма всех его заказов`,
    AVG(subtotal) as `средний чек`,
    MAX(o.created) as `Дата последнего заказа`
from users left join orders o on users.id = o.user_id
group by users.id');
                    print_r($result->fetch_all(MYSQLI_ASSOC));


                }
            }
        }
    }
}

