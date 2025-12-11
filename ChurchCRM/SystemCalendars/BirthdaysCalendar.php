<?php

namespace ChurchCRM\SystemCalendars;

use ChurchCRM\model\ChurchCRM\Event;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\ObjectCollection;

class BirthdaysCalendar implements SystemCalendar
{
    public static function isAvailable(): bool
    {
        return true;
    }

    public function getAccessToken(): bool
    {
        return false;
    }

    public function getBackgroundColor(): string
    {
        return '0000FF';
    }

    public function getForegroundColor(): string
    {
        return 'FFFFFF';
    }

    public function getId(): int
    {
        return 0;
    }

    public function getName(): string
    {
        return gettext('Birthdays');
    }

    public function getEvents(string $start, string $end): ObjectCollection
    {
        $startDate = new \DateTime($start);
        $endDate = new \DateTime($end);

        $people = PersonQuery::create()
            ->filterByBirthDay('', Criteria::NOT_EQUAL)
            ->find();

        $events = new ObjectCollection();
        $events->setModel(Event::class);

        foreach ($people as $person) {
            $month = str_pad($person->getBirthMonth(), 2, '0', STR_PAD_LEFT);
            $day = str_pad($person->getBirthDay(), 2, '0', STR_PAD_LEFT);

            for ($year = (int) $startDate->format('Y'); $year <= (int) $endDate->format('Y'); $year++) {
                $eventDate = "$year-$month-$day";
                
                $eventDateObj = new \DateTime($eventDate);
                if ($eventDateObj >= $startDate && $eventDateObj <= $endDate) {
                    $event = new Event();
                    $event->setId($person->getId());
                    $event->setEditable(false);
                    $event->setStart($eventDate);
                    $age = $person->getAge($eventDate);
                    $event->setTitle($person->getFullName() . ($age ? " ($age)" : ''));
                    $event->setURL($person->getViewURI());

                    $events->push($event);
                }
            }
        }

        return $events;
    }

    public function getEventById(int $Id): ObjectCollection
    {
        $people = PersonQuery::create()
            ->filterByBirthDay('', Criteria::NOT_EQUAL)
            ->filterById($Id)
            ->find();

        return $this->peopleCollectionToEvents($people);
    }

    private function peopleCollectionToEvents(ObjectCollection $People): ObjectCollection
    {
        $events = new ObjectCollection();
        $events->setModel(Event::class);
        foreach ($People as $person) {
            for ($year = (int) date('Y'); $year <= (int) date('Y') + 1; $year++) {
                $birthday = new Event();
                $birthday->setId($person->getId());
                $birthday->setEditable(false);
                $eventDate = $year . '-' . $person->getBirthMonth() . '-' . $person->getBirthDay();
                $birthday->setStart($eventDate);
                $age = $person->getAge($eventDate);
                $birthday->setTitle($person->getFullName() . ($age ? ' (' . $age . ')' : ''));
                $birthday->setURL($person->getViewURI());
                $events->push($birthday);
            }
        }

        return $events;
    }
}
