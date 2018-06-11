<?php

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    public function createWorkLog(array $params = [])
    {
        $workLog = $this->populateEntity(new \App\Entity\WorkLog(), [
            'user' => function () {
                return $this->createUser();
            },
            'startTime' => (new DateTimeImmutable()),
            'endTime' => (new DateTimeImmutable())->add(new DateInterval('PT1S')),
        ], $params);

        $this->persistEntity($workLog);

        return $this->grabEntityFromRepository(\App\Entity\WorkLog::class, [
            'id' => $workLog->getId(),
        ]);
    }

    public function createUser(array $params = [])
    {
        $user = $this->populateEntity(new \App\Entity\User(), [
            'email' => 'user@example.com',
            'firstName' => 'Jan',
            'lastName' => 'Novák',
            'isActive' => true,
            'plainPassword' => 'password',
            'supervisor' => null,
        ], $params);

        $this->persistEntity($user);

        return $this->grabEntityFromRepository(\App\Entity\User::class, [
            'id' => $user->getId(),
        ]);
    }

    public function generateWorkHours($requiredHours) {
        $workHours = [];

        for ($year = 2018; $year <= 2021; ++$year) {
            for ($month = 1; $month <= 12; ++$month) {
                $workHours[] = [
                    'month' => $month,
                    'requiredHours' => $requiredHours,
                    'year' => $year,
                ];
            }
        }

        return $workHours;
    }

    /**
     * @param object $entity
     * @param array $defaultData
     * @param array $params
     * @return object
     */
    private function populateEntity($entity, array $defaultData, array $params)
    {
        foreach ($defaultData as $property => $value) {
            if (isset($params[$property])) {
                if ($params[$property] instanceof Closure) {
                    $newValue = call_user_func($params[$property]);
                } else {
                    $newValue = $params[$property];
                }
            } else {
                if ($value instanceof Closure) {
                    $newValue = call_user_func($value);
                } else {
                    $newValue = $value;
                }
            }

            $entity->{'set' . ucfirst($property)}($newValue);
        }

        return $entity;
    }
}
