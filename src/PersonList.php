<?php
declare(strict_types=1);

namespace App;

use App\Person;
use Exception;
use PDO;

class PersonList
{
    private array $personList = [];
    private DbService $dbService;

    /**
     * Конструктор ведет поиск id людей по всем полям БД
     *
     * @param array|null $conditions contains a list of conditions for selecting elements.
     */
    public function __construct(array $conditions = null)
    {
        $this->dbService = DbService::getInstance();
        $sql = 'SELECT id FROM person';

        if ($conditions && count($conditions) > 0) {
            $sql .= ' WHERE ';
            $conditionCount = count($conditions);
            for ($i = 0; $i < $conditionCount; $i++) {
                $sql .= $conditions[$i];
                if ($i !== $conditionCount - 1) {
                    $sql .= ', ';
                }
            }
        }

        $stmt = $this->dbService->prepare($sql);
        $stmt->execute();
        $this->personList = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Получение массива экземпляров класса 1 из массива с id людей полученного в конструкторе
     *
     * @return array
     * @throws Exception
     */
    public function getPersonsPool(): array
    {
        if (empty($this->personList)){
            return [];
        }
        $joinArray = implode(',' , $this->personList);
        $sql = "SELECT * FROM person WHERE id in ($joinArray)";
        $stmt = $this->dbService->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $persons = [];
        foreach ($result as $personData){
            $persons[] = new Person($personData);
        }
        return $persons;
    }

    /**
     * Удаление людей из БД с помощью экземпляров класса 1 в соответствии с массивом, полученным в конструкторе
     *
     * @return void
     */
    public function deletePersons(): void
    {
        if (empty($this->personList)){
            return;
        }
        $joinArray = implode(',' , $this->personList);
        $sql = "DELETE FROM person WHERE id in ($joinArray)";
        $stmt = $this->dbService->prepare($sql);
        $stmt->execute();
    }

    /**
     * @return array
     */
    public function getPersonList(): array
    {
        return $this->personList;
    }

    /**
     * @param array $personList
     * @return PersonList
     */
    public function setPersonList(array $personList): PersonList
    {
        $this->personList = $personList;
        return $this;
    }
}