<?php
require_once 'Include/Config.php';
require_once 'Include/Functions.php';

require_once __DIR__ . '/vendor/autoload.php';

use ChurchCRM\model\ChurchCRM\PersonQuery;
use ChurchCRM\model\ChurchCRM\GroupQuery;
use ChurchCRM\model\ChurchCRM\Person2group2roleP2g2r;
use ChurchCRM\model\ChurchCRM\Person2group2roleP2g2rQuery;
use ChurchCRM\Authentication\AuthenticationManager;

header('Content-Type: application/json');

if (!AuthenticationManager::getCurrentUser()->isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Fungsi bantu
function calculateAge($birthDate) {
    $dob = new DateTime($birthDate);
    $today = new DateTime();
    return $today->diff($dob)->y;
}

function getGroupNamesByAge($age) {
    $groups = [];
    if ($age <= 15) {
        $groups[] = "Anak";
        if ($age >= 13) $groups[] = "Pra Remaja";
    }
    if ($age >= 16 && $age <= 19) $groups[] = "Remaja";
    if ($age >= 20 && $age <= 30) $groups[] = "Pemuda";
    if ($age >= 31 && $age <= 39) $groups[] = "Dewasa Muda";
    if ($age >= 40 && $age <= 49) $groups[] = "Dewasa Madya";
    if ($age >= 50 && $age <= 59) $groups[] = "Dewasa Senior";
    if ($age >= 60) $groups[] = "Lansia";
    return $groups;
}

try {
    // Hapus semua relasi dari grup type = 1
    $groupsToReset = GroupQuery::create()->filterByType(1)->find();
    foreach ($groupsToReset as $group) {
        Person2group2roleP2g2rQuery::create()
            ->filterByGroupId($group->getId())
            ->delete();
    }

    // Assign ulang
    $people = PersonQuery::create()->find();
    $assigned = 0;

    foreach ($people as $person) {
        $birthDay = $person->getBirthDay();
        $birthMonth = $person->getBirthMonth();
        $birthYear = $person->getBirthYear();

        if (!$birthDay || !$birthMonth || !$birthYear) {
            continue;
        }

        $dob = new DateTime("$birthYear-$birthMonth-$birthDay");
        $age = calculateAge($dob->format('Y-m-d'));
        $groupNames = getGroupNamesByAge($age);

        foreach ($groupNames as $groupName) {
            $group = GroupQuery::create()
                ->filterByName($groupName)
                ->filterByType(1)
                ->findOne();

            if (!$group) continue;

            $exists = Person2group2roleP2g2rQuery::create()
                ->filterByPersonId($person->getId())
                ->filterByGroupId($group->getId())
                ->findOne();

            if (!$exists) {
                $newRel = new Person2group2roleP2g2r();
                $newRel->setPersonId($person->getId());
                $newRel->setGroupId($group->getId());
                $newRel->setRoleId(0);
                $newRel->save();
                $assigned++;
            }
        }
    }

    echo json_encode(['success' => true, 'assigned' => $assigned]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}