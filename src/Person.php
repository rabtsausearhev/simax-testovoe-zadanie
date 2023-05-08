<?php
declare(strict_types=1);

namespace App;

use DateTime;
use Exception;
use stdClass;

class Person
{
    private ?int $id = null;
    private string $name;
    private string $surname;
    private string $birthday;
    private int $sex;
    private string $birth_city;
    private DbService $dbService;

    /**
     * Конструктор класса либо создает человека в БД с заданной информацией, либо берет информацию из БД по id
     *
     * @param array|int $data
     *  if $data is array
     *  - schema:
     *      ['name'=>string, 'surname'=>string, 'birthday'=> string, 'sex'=>int(0 or 1), 'birth_city'=>string]
     * @throws Exception
     */
    public function __construct(int|array $data)
    {
        $this->dbService = DbService::getInstance();
        if (gettype($data) === 'integer') {
            if ($person = $this->find($data)) {
                $this->id = $data;
                $this->name = $person['name'];
                $this->surname = $person['surname'];
                $this->birthday = $person['birthday'];
                $this->sex = $person['sex'];
                $this->birth_city = $person['birth_city'];
            } else {
                throw new Exception('Person not found.');
            }
        } elseif (gettype($data) === 'array') {
            if($this->dataValidation($data)){
                $this->id = $data['id'] ?? null;
                $this->name = $data['name'];
                $this->surname = $data['surname'];
                $this->birthday = $data['birthday'];
                $this->sex = $data['sex'];
                $this->birth_city = $data['birth_city'];
                if ($this->id === null) {
                    $this->save();
                }
            }
            throw new Exception('Invalid parameters "name" or "surname"');
        } else {
            throw new Exception('Check the arguments type to create the Person class.');
        }
    }

    /**
     * Сохранение полей экземпляра класса в БД
     *
     * @return void
     */
    public function save(): void
    {
        if ($this->id !== null && $this->find($this->id)) {
            $stmt = $this->dbService->prepare('UPDATE person SET name = :name, surname = :surname, birthday = :birthday, sex = :sex, birth_city = :birth_city WHERE id = :id');
            $stmt->execute([
                'id' => $this->id,
                'name' => $this->name,
                'surname' => $this->surname,
                'birthday' => $this->birthday,
                'sex' => $this->sex,
                'birth_city' => $this->birth_city
            ]);
            return;
        }
        $stmt = $this->dbService->prepare('INSERT person (name, surname, birthday, sex, birth_city) VALUES (:name, :surname, :birthday, :sex, :birth_city)');
        $stmt->execute([
            'name' => $this->name,
            'surname' => $this->surname,
            'birthday' => $this->birthday,
            'sex' => $this->sex,
            'birth_city' => $this->birth_city
        ]);
        $this->id = (int)$this->dbService->lastInsertId();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function find(int $id): mixed
    {
        $stmt = $this->dbService->prepare('SELECT * FROM person WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Удаление человека из БД в соответствии с id объекта
     *
     * @param int $id
     */
    public function remove(int $id): void
    {
        $stmt = $this->dbService->prepare('DELETE FROM person WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /**
     * static преобразование даты рождения в возраст (полных лет)
     *
     * @param string $birthday
     * @return int
     * @throws Exception
     */
    public static function getAge(string $birthday): int
    {
        $dayOfBirthday = new DateTime($birthday);
        $now = new DateTime();
        return $now->diff($dayOfBirthday)->y;
    }

    /**
     * static преобразование пола из двоичной системы в текстовую (муж, жен)
     *
     * @param int $sex
     * @return string
     */
    public static function getSexAsString(int $sex): string
    {
        return ($sex === 0) ? 'woman' : 'man';
    }

    /**
     * Форматирование человека с преобразованием возраста и (или) пола (п.3 и п.4) в зависимости
     * от параметров (возвращает новый экземпляр stdClass со всеми полями изначального класса)
     *
     * @return stdClass
     * @throws Exception
     */
    public function format(): stdClass
    {
        $form = new stdClass();
        $form->id = $this->id;
        $form->name = $this->name;
        $form->surname = $this->surname;
        $form->birthday = $this->birthday;
        $form->age = $this::getAge($this->birthday);
        $form->sex = $this::getSexAsString($this->sex);
        $form->birth_city = $this->birth_city;
        return $form;
    }

    /**
     * @param $data
     * @return bool
     */
    public function dataValidation($data): bool
    {
        if(!ctype_alpha($data['name']??'')){
            return false;
        }
        if(!ctype_alpha($data['surname']??'')){
            return false;
        }

        return true;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Person
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getSurname(): string
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     * @return Person
     */
    public function setSurname(string $surname): static
    {
        $this->surname = $surname;
        return $this;
    }

    /**
     * @return int
     */
    public function getSex(): int
    {
        return $this->sex;
    }

    /**
     * @param int $sex
     * @return Person
     */
    public function setSex(int $sex): static
    {
        $this->sex = $sex;
        return $this;
    }

    /**
     * @return string
     */
    public function getBirthday(): string
    {
        return $this->birthday;
    }

    /**
     * @param string $birthday
     * @return Person
     */
    public function setBirthday(string $birthday): static
    {
        $this->birthday = $birthday;
        return $this;
    }

    /**
     * @return string
     */
    public function getBirthCity(): string
    {
        return $this->birth_city;
    }

    /**
     * @param string $birth_city
     * @return Person
     */
    public function setBirthCity(string $birth_city): static
    {
        $this->birth_city = $birth_city;
        return $this;
    }
}