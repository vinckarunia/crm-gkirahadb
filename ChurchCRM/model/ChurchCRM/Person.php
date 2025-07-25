<?php

namespace ChurchCRM\model\ChurchCRM;

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\dto\Photo;
use ChurchCRM\dto\SystemConfig;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Emails\notifications\NewPersonOrFamilyEmail;
use ChurchCRM\model\ChurchCRM\Base\Person as BasePerson;
use ChurchCRM\PhotoInterface;
use ChurchCRM\Service\GroupService;
use ChurchCRM\Utils\GeoUtils;
use ChurchCRM\Utils\LoggerUtils;
use DateTime;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Map\TableMap;

/**
 * Skeleton subclass for representing a row from the 'person_per' table.
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class Person extends BasePerson implements PhotoInterface
{
    public const SELF_REGISTER = -1;
    public const SELF_VERIFY = -2;
    private ?Photo $photo = null;

    public function getFullName(): string
    {
        return $this->getFormattedName(SystemConfig::getValue('iPersonNameStyle'));
    }

    public function isMale(): bool
    {
        return $this->getGender() == 1;
    }

    public function isFemale(): bool
    {
        return $this->getGender() == 2;
    }

    public function getGenderName(): string
    {
        switch (strtolower($this->getGender())) {
            case 1:
                return gettext('Male');
            case 2:
                return gettext('Female');
            default:
                return gettext('Unassigned');
        }
    }

    public function hideAge(): bool
    {
        return $this->getFlags() === 1 || empty($this->getBirthYear());
    }

    public function getBirthDate(): ?\DateTimeImmutable
    {
        if (
            !empty($this->getBirthDay()) &
            !empty($this->getBirthMonth())
        ) {
            $birthYear = $this->getBirthYear();
            if (empty($birthYear)) {
                $birthYear = 0;
            }

            return \DateTimeImmutable::createFromFormat('Y-m-d', $birthYear . '-' . $this->getBirthMonth() . '-' . $this->getBirthDay());
        }

        return null;
    }

    public function getFormattedBirthDate()
    {
        $birthDate = $this->getBirthDate();
        if (!$birthDate) {
            return false;
        }
        if ($this->hideAge()) {
            return $birthDate->format(SystemConfig::getValue('sDateFormatNoYear'));
        } else {
            return $birthDate->format(SystemConfig::getValue('sDateFormatLong'));
        }
    }

    public function getViewURI(): string
    {
        return SystemURLs::getRootPath() . '/PersonView.php?PersonID=' . $this->getId();
    }

    public function getFamilyRole()
    {
        $familyRole = null;
        $roleId = $this->getFmrId();
        if ($roleId !== 0) {
            $familyRole = ListOptionQuery::create()->filterById(2)->filterByOptionId($roleId)->findOne();
        }

        return $familyRole;
    }

    public function getFamilyRoleName(): string
    {
        $roleName = 'Unassigned';
        $role = $this->getFamilyRole();
        if ($role !== null) {
            $roleName = $this->getFamilyRole()->getOptionName();
        }

        return $roleName;
    }

    public function getClassification()
    {
        $classification = null;
        $clsId = $this->getClsId();
        if (!empty($clsId)) {
            $classification = ListOptionQuery::create()->filterById(1)->filterByOptionId($clsId)->findOne();
        }

        return $classification;
    }

    public function getClassificationName()
    {
        $classificationName = 'Unassigned';
        $classification = $this->getClassification();
        if ($classification !== null) {
            $classificationName = $classification->getOptionName();
        }

        return $classificationName;
    }

    public function postInsert(ConnectionInterface $con = null): void
    {
        $this->createTimeLineNote('create');
        if (!empty(SystemConfig::getValue('sNewPersonNotificationRecipientIDs'))) {
            $NotificationEmail = new NewPersonOrFamilyEmail($this);
            if (!$NotificationEmail->send()) {
                LoggerUtils::getAppLogger()->warning(gettext('New Person Notification Email Error') . ' :' . $NotificationEmail->getError());
            }
        }
    }

    public function postUpdate(ConnectionInterface $con = null): void
    {
        if (!empty($this->getDateLastEdited())) {
            $this->createTimeLineNote('edit');
        }
    }

    private function createTimeLineNote(string $type): void
    {
        $note = new Note();
        $note->setPerId($this->getId());
        $note->setType($type);
        $note->setDateEntered(new DateTime());

        switch ($type) {
            case 'create':
                $note->setText(gettext('Created'));
                $note->setEnteredBy($this->getEnteredBy());
                $note->setDateEntered($this->getDateEntered());
                break;
            case 'edit':
                $note->setText(gettext('Updated'));
                $note->setEnteredBy($this->getEditedBy());
                $note->setDateEntered($this->getDateLastEdited());
                break;
        }

        $note->save();
    }

    public function isUser(): bool
    {
        $user = UserQuery::create()->findPk($this->getId());

        return $user !== null;
    }

    /**
     * Get full address string of a person.
     *
     * If the address is not defined on the person record, return family address if one exists.
     */
    public function getAddress(): string
    {
        if (!empty($this->getAddress1())) {
            $address = [];
            $tmp = $this->getAddress1();
            if (!empty($this->getAddress2())) {
                $tmp = $tmp . ' ' . $this->getAddress2();
            }

            $address[] = $tmp;
            if (!empty($this->getCity())) {
                $address[] = $this->getCity() . ',';
            }
            if (!empty($this->getState())) {
                $address[] = $this->getState();
            }
            if (!empty($this->getZip())) {
                $address[] = $this->getZip();
            }
            if (!empty($this->getCountry())) {
                $address[] = $this->getCountry();
            }

            return implode(' ', $address);
        } elseif ($this->getFamily()) {
            return $this->getFamily()->getAddress();
        }

        return '';
    }

    /**
     * Get name of a person family.
     */
    public function getFamilyName(): string
    {
        if ($this->getFamily()) {
            return $this->getFamily()
              ->getName();
        }
        //if it reaches here, no family name found. return empty family name
        return '';
    }

    /**
     * Get name of a person family.
     */
    public function getFamilyCountry(): string
    {
        if ($this->getFamily()) {
            return $this->getFamily()
              ->getCountry();
        }
        //if it reaches here, no country found. return empty country
        return '';
    }

    /**
     * Get Phone of a person family.
     * 0 = Home
     * 1 = Work
     * 2 = Cell.
     */
    public function getFamilyPhone(int $type): string
    {
        switch ($type) {
            case 0:
                if ($this->getFamily()) {
                    return $this->getFamily()
                    ->getHomePhone();
                }
                break;
            case 1:
                if ($this->getFamily()) {
                    return $this->getFamily()
                    ->getWorkPhone();
                }
                break;
            case 2:
                if ($this->getFamily()) {
                    return $this->getFamily()
                    ->getCellPhone();
                }
                break;
        }
        //if it reaches here, no phone found. return empty phone
        return '';
    }

    /**
     * If person address found, return latitude and Longitude of person address
     * Otherwise, return the family latitude and Longitude.
     */
    public function getLatLng(): array
    {
        $bUseFamilyAddress = !empty($this->getAddress1()) && $this->getFamily();

        $lat = 0;
        $lng = 0;
        if ($bUseFamilyAddress && $this->getFamily()->hasLatitudeAndLongitude()) {
            $lat = $this->getFamily()->getLatitude();
            $lng = $this->getFamily()->getLongitude();
        } else {
            // if person address is empty, this will get Family address
            $sFullAddress = $this->getAddress();

            $latLng = GeoUtils::getLatLong($sFullAddress);
            if (!empty($latLng['Latitude']) && !empty($latLng['Longitude'])) {
                $lat = $latLng['Latitude'];
                $lng = $latLng['Longitude'];
            }

            if ($bUseFamilyAddress) {
                // if we are using a family address, cache the result to avoid additional work in the future
                $this->getFamily()->updateLanLng();
            }
        }

        return [
            'Latitude'  => $lat,
            'Longitude' => $lng,
        ];
    }

    public function deletePhoto(): bool
    {
        if (AuthenticationManager::getCurrentUser()->isDeleteRecordsEnabled()) {
            if ($this->getPhoto()->delete()) {
                $note = new Note();
                $note->setText(gettext('Profile Image Deleted'));
                $note->setType('photo');
                $note->setEntered(AuthenticationManager::getCurrentUser()->getId());
                $note->setPerId($this->getId());
                $note->save();

                return true;
            }
        }

        return false;
    }

    public function getPhoto(): Photo
    {
        if (!$this->photo) {
            $this->photo = new Photo('Person', $this->getId());
        }

        return $this->photo;
    }

    public function setImageFromBase64($base64): bool
    {
        if (AuthenticationManager::getCurrentUser()->isEditRecordsEnabled()) {
            $note = new Note();
            $note->setText(gettext('Profile Image uploaded'));
            $note->setType('photo');
            $note->setEntered(AuthenticationManager::getCurrentUser()->getId());
            $this->getPhoto()->setImageFromBase64($base64);
            $note->setPerId($this->getId());
            $note->save();

            return true;
        }

        return false;
    }

    /**
     * Returns a string of a person's full name, formatted as specified by $Style
     * $Style = 0  :  "Title FirstName MiddleName LastName, Suffix"
     * $Style = 1  :  "Title FirstName MiddleInitial. LastName, Suffix"
     * $Style = 2  :  "LastName, Title FirstName MiddleName, Suffix"
     * $Style = 3  :  "LastName, Title FirstName MiddleInitial., Suffix"
     * $Style = 4  :  "FirstName MiddleName LastName"
     * $Style = 5  :  "Title FirstName LastName"
     * $Style = 6  :  "LastName, Title FirstName"
     * $Style = 7  :  "LastName FirstName"
     * $Style = 8  :  "LastName, FirstName Middlename".
     */
    public function getFormattedName(int $Style): string
    {
        $nameString = '';
        switch ($Style) {
            case 0:
                if ($this->getTitle()) {
                    $nameString .= $this->getTitle() . ' ';
                }
                $nameString .= $this->getFirstName();
                if ($this->getMiddleName()) {
                    $nameString .= ' ' . $this->getMiddleName();
                }
                if ($this->getLastName()) {
                    $nameString .= ' ' . $this->getLastName();
                }
                if ($this->getSuffix()) {
                    $nameString .= ', ' . $this->getSuffix();
                }
                break;

            case 1:
                if ($this->getTitle()) {
                    $nameString .= $this->getTitle() . ' ';
                }
                $nameString .= $this->getFirstName();
                if ($this->getMiddleName()) {
                    $nameString .= ' ' . strtoupper(mb_substr($this->getMiddleName(), 0, 1, 'UTF-8')) . '.';
                }
                if ($this->getLastName()) {
                    $nameString .= ' ' . $this->getLastName();
                }
                if ($this->getSuffix()) {
                    $nameString .= ', ' . $this->getSuffix();
                }
                break;

            case 2:
                if ($this->getLastName()) {
                    $nameString .= $this->getLastName() . ', ';
                }
                if ($this->getTitle()) {
                    $nameString .= $this->getTitle() . ' ';
                }
                $nameString .= $this->getFirstName();
                if ($this->getMiddleName()) {
                    $nameString .= ' ' . $this->getMiddleName();
                }
                if ($this->getSuffix()) {
                    $nameString .= ', ' . $this->getSuffix();
                }
                break;

            case 3:
                if ($this->getLastName()) {
                    $nameString .= $this->getLastName() . ', ';
                }
                if ($this->getTitle()) {
                    $nameString .= $this->getTitle() . ' ';
                }
                $nameString .= $this->getFirstName();
                if ($this->getMiddleName()) {
                    $nameString .= ' ' . strtoupper(mb_substr($this->getMiddleName(), 0, 1, 'UTF-8')) . '.';
                }
                if ($this->getSuffix()) {
                    $nameString .= ', ' . $this->getSuffix();
                }
                break;

            case 4:
                $nameString .= $this->getFirstName();
                if ($this->getMiddleName()) {
                    $nameString .= ' ' . $this->getMiddleName();
                }
                if ($this->getLastName()) {
                    $nameString .= ' ' . $this->getLastName();
                }
                break;

            case 5:
                if ($this->getTitle()) {
                    $nameString .= $this->getTitle() . ' ';
                }
                $nameString .= $this->getFirstName();
                if ($this->getLastName()) {
                    $nameString .= ' ' . $this->getLastName();
                }
                break;

            case 6:
                if ($this->getLastName()) {
                    $nameString .= $this->getLastName() . ', ';
                }
                if ($this->getTitle()) {
                    $nameString .= $this->getTitle() . ' ';
                }
                $nameString .= $this->getFirstName();
                break;

            case 7:
                if ($this->getLastName()) {
                    $nameString .= $this->getLastName() . ' ';
                }
                if ($this->getFirstName()) {
                    $nameString .= $this->getFirstName();
                } else {
                    $nameString = trim($nameString);
                }
                break;

            case 8:
                if ($this->getLastName()) {
                    $nameString .= $this->getLastName();
                }
                if ($this->getFirstName()) {
                    if (!$nameString) { // no first name
                        $nameString = $this->getFirstName();
                    } else {
                        $nameString .= ', ' . $this->getFirstName();
                    }
                }
                if ($this->getMiddleName()) {
                    $nameString .= ' ' . $this->getMiddleName();
                }
                break;
            default:
                $nameString = trim($this->getFullName());
        }

        return $nameString;
    }

    public function preDelete(ConnectionInterface $con = null)
    {
        $this->deletePhoto();

        $obj = Person2group2roleP2g2rQuery::create()->filterByPerson($this)->find($con);
        if ($obj->count() > 0) {
            $groupService = new GroupService();
            foreach ($obj as $group2roleP2g2r) {
                $groupService->removeUserFromGroup($group2roleP2g2r->getGroupId(), $group2roleP2g2r->getPersonId());
            }
        }

        $perCustom = PersonCustomQuery::create()->findPk($this->getId(), $con);
        if ($perCustom !== null) {
            $perCustom->delete($con);
        }

        $user = UserQuery::create()->findPk($this->getId(), $con);
        if ($user !== null) {
            $user->delete($con);
        }

        PersonVolunteerOpportunityQuery::create()->filterByPersonId($this->getId())->delete($con);

        PropertyQuery::create()
            ->filterByProClass('p')
            ->useRecordPropertyQuery()
            ->filterByRecordId($this->getId())
            ->delete($con);

        NoteQuery::create()->filterByPerson($this)->delete($con);

        return parent::preDelete($con);
    }

    public function getProperties()
    {
        return PropertyQuery::create()
          ->filterByProClass('p')
          ->useRecordPropertyQuery()
          ->filterByRecordId($this->getId())
          ->find();
    }

    //  return array of person properties
    // created for the person-list.php datatable
    /**
     * @return string[]
     */
    public function getPropertiesString(): array
    {
        $personProperties = PropertyQuery::create()
          ->filterByProClass('p')
          ->leftJoinRecordProperty()
          ->where('r2p_record_ID=' . $this->getId())
          ->find();

        $PropertiesList = [];
        foreach ($personProperties as $element) {
            $PropertiesList[] = $element->getProName();
        }

        return $PropertiesList;
    }

    // return array of person custom fields
    // created for the person-list.php datatable
    /**
     * @return string[]
     */
    public function getCustomFields($allPersonCustomFields, array $customMapping, array &$CustomList, $name_func): string
    {
        $rawQry = PersonCustomQuery::create();
        foreach ($allPersonCustomFields as $customfield) {
            if (AuthenticationManager::getCurrentUser()->isEnabledSecurity($customfield->getFieldSecurity())) {
                $rawQry->withColumn($customfield->getId());
            }
        }

        $thisPersonCustomFields = $rawQry->findOneByPerId($this->getId());

        $personCustom = [];
        if ($thisPersonCustomFields) {
            foreach ($thisPersonCustomFields->getVirtualColumns() as $column => $value) {
                if (!empty($value)) {
                    $fieldName = $customMapping[$column]['Name'];
                    $CustomList[$fieldName] += 1;

                    if (array_key_exists($value, $customMapping[$column]['Elements'])) {
                    $resolvedValue = $name_func($fieldName, $customMapping[$column]['Elements'][$value]);

                    $cleanedValue = preg_replace('/^' . preg_quote($fieldName, '/') . ':\s*/i', '', $resolvedValue);

                        $display = '<strong>' . trim($fieldName) . ': </strong>' . trim($cleanedValue);
                    } else {
                        $display = '<strong>' . trim($fieldName) . ': </strong>' . trim($value);
                    }

                $personCustom[] = $display;
                $CustomList[$display] += 1;
                }
            }
        }

        return implode('<br>', $personCustom);
    }

    // return array of person groups
    // created for the person-list.php datatable
    /**
     * @return string[]
     */
    public function getGroups(): array
    {
        $GroupList = GroupQuery::create()
        ->leftJoinPerson2group2roleP2g2r()
        ->where('p2g2r_per_ID=' . $this->getId())
        ->find();

        $group = [];
        foreach ($GroupList as $element) {
            $group[] = $element->getName();
        }

        return $group;
    }

    public function getNumericCellPhone(): string
    {
        return '1' . preg_replace('/[^\.0-9]/', '', $this->getCellPhone());
    }

    public function postSave(ConnectionInterface $con = null): void
    {
        $this->getPhoto()->refresh();

        parent::postSave($con);
    }

    public function getAge(?string $date = null): ?string
    {
        if ($this->getBirthYear() === null) {
            return null;
        }

        $birthDate = $this->getBirthDate();

        if (!$birthDate instanceof \DateTimeImmutable || $this->hideAge()) {
            return false;
        }
        $now = $date == null ? new \DateTimeImmutable('today') : \DateTimeImmutable::createFromFormat('Y-m-d', $date);
        $age = date_diff($now, $birthDate);

        if ($age->y < 1) {
            return sprintf(ngettext('%d month old', '%d months old', $age->m), $age->m);
        }

        return sprintf(ngettext('%d year old', '%d years old', $age->y), $age->y);
    }

    public function getNumericAge(): int
    {
        $birthDate = $this->getBirthDate();
        if (!$birthDate instanceof \DateTimeImmutable || $this->hideAge()) {
            return false;
        }

        $now = date_create('today');
        $age = date_diff($now, $birthDate);
        if ($age->y < 1) {
            $ageValue = 0;
        } else {
            $ageValue = $age->y;
        }

        return $ageValue;
    }

    public function getFullNameWithAge(): string
    {
        return $this->getFullName() . ' ' . $this->getAge();
    }

    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = [], $includeForeignObjects = false)
    {
        $array = parent::toArray();
        $array['Address'] = $this->getAddress();
        $array['FullName'] = $this->getFullName();

        return $array;
    }

    public function getThumbnailURL(): string
    {
        return SystemURLs::getRootPath() . '/api/person/' . $this->getId() . '/thumbnail';
    }

    public function getEmail(): ?string
    {
        if (parent::getEmail() == null) {
            $family = $this->getFamily();
            if ($family != null) {
                return $family->getEmail();
            }
        }

        return parent::getEmail();
    }

    /**
     * Returns a string of a person's full name initial, formatted as specified by $Style
     * $Style = 0  :  "One character from FirstName and one character from LastName"
     * $Style = 1  :  "Two characters from FirstName"
     */
    public function getInitial(int $Style): string
    {
        $initialString = '';
        switch ($Style) {
            case 0:
                $initialString .= mb_strtoupper(mb_substr($this->getFirstName(), 0, 1));
                $initialString .= mb_strtoupper(mb_substr($this->getLastName(), 0, 1));
                break;
            case 1:
                $fullNameArr = $this->getFirstName();
                $initialString .= mb_strtoupper(mb_substr($fullNameArr, 0, 2));
        }

        return $initialString;
    }
}
