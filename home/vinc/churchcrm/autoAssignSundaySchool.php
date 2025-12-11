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
    if ($age < 3) $groups[] = "Batita";
    if ($age >= 3 && $age <= 5) $groups[] = "Balita";
    if ($age == 6) $groups[] = "Kelas 1";
    if ($age == 7) $groups[] = "Kelas 2";
    if ($age == 8) $groups[] = "Kelas 3";
    if ($age == 9) $groups[] = "Kelas 4";
    if ($age == 10) $groups[] = "Kelas 5";
    if ($age == 11) $groups[] = "Kelas 6";
    if ($age == 12) $groups[] = "Pra-Remaja 1";
    if ($age == 13) $groups[] = "Pra-Remaja 2";
    if ($age >= 14 && $age <= 19) $groups[] = "Remaja";
    return $groups;
}

try {
    // Hapus semua relasi dari grup type = 2
    $groupsToReset = GroupQuery::create()->filterByType(4)->find();
    foreach ($groupsToReset as $group) {
        Person2group2roleP2g2rQuery::create()
            ->filterByGroupId($group->getId())
            ->delete();
    }

    // Assign ulang
    $people = PersonQuery::create()
        ->filterByClsId(1) // hanya ambil member
        ->find();
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
                ->filterByType(4)
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
                $newRel->setRoleId(2);
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