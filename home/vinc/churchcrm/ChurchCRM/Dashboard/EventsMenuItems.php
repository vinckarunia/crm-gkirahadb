<?php

namespace ChurchCRM\Dashboard;

use ChurchCRM\model\ChurchCRM\EventQuery;
use ChurchCRM\model\ChurchCRM\FamilyQuery;
use ChurchCRM\model\ChurchCRM\Map\FamilyTableMap;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use ChurchCRM\model\ChurchCRM\PersonCustomQuery;
use ChurchCRM\model\ChurchCRM\PersonCustomMasterQuery;
use ChurchCRM\model\ChurchCRM\ListOptionQuery;
use Propel\Runtime\ActiveQuery\Criteria;

class EventsMenuItems implements DashboardItemInterface
{
    public static function getDashboardItemName(): string
    {
        return 'EventsCounters';
    }

    public static function getDashboardItemValue(): array
    {
        return [
            'Events'                => self::getNumberEventsOfToday(),
            'Birthdays'             => self::getNumberBirthDates(),
            'Birthdays This Week'   => self::getNumberBirthDatesThisWeek(),
            'Birthdays Next Week'   => self::getNumberBirthDatesNextWeek(),
            'Anniversaries'         => self::getNumberAnniversaries(),
        ];
    }

    public static function shouldInclude(string $PageName): bool
    {
        return true; // this ID would be found on all pages.
    }

    private static function getNumberEventsOfToday()
    {
        $start_date = date('Y-m-d ') . ' 00:00:00';
        $end_date = date('Y-m-d H:i:s', strtotime($start_date . ' +1 day'));

        return EventQuery::create()
            ->where("event_start <= '" . $start_date . "' AND event_end >= '" . $end_date . "'") /* the large events */
            ->_or()->where("event_start>='" . $start_date . "' AND event_end <= '" . $end_date . "'") /* the events of the day */
            ->count();
    }

    private static function getNumberBirthDates()
    {
        return PersonQuery::create()
            ->filterByClsId(1)
            ->filterByBirthMonth(date('m'))
            ->filterByBirthDay(date('d'))
            ->count();
    }

    private static function getCustomFieldById($id)
    {
        return \ChurchCRM\model\ChurchCRM\PersonCustomMasterQuery::create()
            ->filterById($id)
            ->findOne();
    }

    private static function getNumberBirthDatesThisWeek()
    {
        $start = (new \DateTime())->modify('last sunday')->setTime(0, 0);
        $end = (clone $start)->modify('+6 days')->setTime(23, 59, 59);

        $birthdaysThisWeek = [];

        for ($day = clone $start; $day <= $end; $day->modify('+1 day')) {
            $dateKey = $day->format('Y-m-d');

            $c17FieldMeta = PersonCustomMasterQuery::create()
                ->findOneById('c17');

            $optionListId = $c17FieldMeta->getSpecial();

            $optionMap = [];
            $options = ListOptionQuery::create()
                ->filterById($optionListId)
                ->find();

            foreach ($options as $option) {
                $optionMap[$option->getOptionId()] = $option->getOptionName();
            }

            $people = PersonQuery::create()
                ->filterByClsId(1)
                ->filterByBirthMonth($day->format('m'))
                ->filterByBirthDay($day->format('d'))
                ->join('PersonCustom')
                ->withColumn("
                    (SELECT l.lst_OptionName 
                    FROM list_lst l 
                    JOIN person_custom_master pcm
                    ON pcm.custom_Field = 'c17'
                    WHERE l.lst_ID = pcm.custom_Special 
                    AND l.lst_OptionID = person_custom.c17
                    )", 'Region'
                )
                ->select(['Id', 'FirstName', 'MiddleName', 'LastName', 'Region'])
                ->find()
                ->toArray();

            $birthdaysThisWeek[$dateKey] = [
                'count' => count($people),
                'people' => $people
            ];
        }

        return $birthdaysThisWeek;
    }

    private static function getNumberBirthDatesNextWeek()
    {
        $start = (new \DateTime())->modify('this sunday')->setTime(0, 0);
        $end = (clone $start)->modify('+6 days')->setTime(23, 59, 59);

        $birthdaysNextWeek = [];

        for ($day = clone $start; $day <= $end; $day->modify('+1 day')) {
            $dateKey = $day->format('Y-m-d');

            $people = PersonQuery::create()
                ->filterByClsId(1)
                ->filterByBirthMonth($day->format('m'))
                ->filterByBirthDay($day->format('d'))
                ->join('PersonCustom')
                ->withColumn("
                    (SELECT l.lst_OptionName 
                    FROM list_lst l 
                    JOIN person_custom_master pcm
                    ON pcm.custom_Field = 'c17'
                    WHERE l.lst_ID = pcm.custom_Special 
                    AND l.lst_OptionID = person_custom.c17
                    )", 'Region'
                )
                ->select(['Id', 'FirstName', 'MiddleName', 'LastName', 'Region'])
                ->find()
                ->toArray();

            $birthdaysNextWeek[$dateKey] = [
                'count' => count($people),
                'people' => $people
            ];
        }

        return $birthdaysNextWeek;
    }

    private static function getNumberAnniversaries()
    {
        return $families = FamilyQuery::create()
            ->filterByDateDeactivated(null)
            ->filterByWeddingdate(null, Criteria::NOT_EQUAL)
            ->addUsingAlias(FamilyTableMap::COL_FAM_WEDDINGDATE, 'MONTH(' . FamilyTableMap::COL_FAM_WEDDINGDATE . ') =' . date('m'), Criteria::CUSTOM)
            ->addUsingAlias(FamilyTableMap::COL_FAM_WEDDINGDATE, 'DAY(' . FamilyTableMap::COL_FAM_WEDDINGDATE . ') =' . date('d'), Criteria::CUSTOM)
            ->orderByWeddingdate('DESC')
            ->count();
    }
}
