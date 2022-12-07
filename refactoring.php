<?php
declare(strict_types=1);
/*2. Сделайте рефакторинг
...
$questionsQ = $mysqli->query('SELECT * FROM questions WHERE catalog_id='. $catId);
$result = array();
while ($question = $questionsQ->fetch_assoc()) {
    $userQ = $mysqli->query('SELECT name, gender FROM users WHERE id='. $question['user_id']);
    $user = $userQ->fetch_assoc();
    $result[] = array('question'=>$question, 'user'=>$user);
    $userQ->free();
}
$questionsQ->free();*/


$connection = new mysqli("localhost", "my_user", "my_password", "world");

/**
 * @param mysqli $connection
 * @param int $categoryId
 * @return array
 * @throws InvalidArgumentException
 */
function getQuestionsWithUsersByCategoryId(mysqli $connection, int $categoryId = 1):array{
    $result = [];
    try {
        if ($categoryId < 1) {
            throw new InvalidArgumentException('Индекс категории должен быть натуральным чмслом');
        }
        $query = 'SELECT questions.*, users.name, users.gender FROM questions 
    LEFT JOIN users ON questions.user_id=users.id WHERE catalog_id=?';
        $statement = $connection->prepare($query);
        $statement->bind_param('i', $categoryId);
        if(!$statement->execute()){
            throw new mysqli_sql_exception('Не удалось выполнить запрос');
        }
        $resultSet = $statement->get_result();
        while ($aggregate = $resultSet->fetch_assoc()) {
            $user = [
                //нет схемы таблиц, подразумеваем что имя есть в обеих таблицах, а пол только у пользователей.
                'name' => $aggregate['users.name'],
                'gender' => $aggregate['gender']
            ];
            unset($aggregate['users.name'], $aggregate['gender']);
            $result[] = [
                'question' => $aggregate,
                'user' => $user
            ];
        }
        $resultSet->free();
    } catch (Throwable $throwable) {
        //логируем ошибку и/или выбрасываем обработанное исключение
        throw new InvalidArgumentException('Ошибка получения списка вопросов: ' . $throwable->getMessage(),
            previous: $throwable);
    } finally {
        //возвращаем обработанный результат
        return $result;
    }
}

print_r([
   'default category'=>getQuestionsWithUsersByCategoryId($connection),
   '34'=>getQuestionsWithUsersByCategoryId($connection, 34),
   'wrong category'=>getQuestionsWithUsersByCategoryId($connection, 'wrong category')
]);


