<?php

namespace ChurchCRM;

require_once 'Include/Config.php';
require_once 'Include/Functions.php';

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\dto\SystemConfig;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\model\ChurchCRM\FamilyCustom;
use ChurchCRM\model\ChurchCRM\FamilyQuery;
use ChurchCRM\model\ChurchCRM\Note;
use ChurchCRM\model\ChurchCRM\PersonCustom;
use ChurchCRM\model\ChurchCRM\PersonQuery;
use ChurchCRM\Utils\InputUtils;
use ChurchCRM\Utils\RedirectUtils;
use ChurchCRM\Utils\LoggerUtils;

AuthenticationManager::redirectHomeIfNotAdmin();

$logger = LoggerUtils::getAppLogger();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// // Create debug log file
// $debug_file = sys_get_temp_dir() . '/csv_import_debug.log';
// function debug_log($message) {
//     global $debug_file;
//     file_put_contents($debug_file, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
// }

// debug_log("=== CSV Import Debug Started ===");

/**
 * A monogamous society is assumed, however  it can be patriarchal or matriarchal
 **/
class Family
{
    public array $Members = [];       // array for member data
    public int $MemberCount = 0;   // obvious
    public string $WeddingDate = '';   // one per family
    public string $Phone = '';         // one per family
    public int $Envelope = 0;      // one per family
    public int $_nAdultMale = 0;   // if one adult male
    public int $_nAdultFemale = 0; // and 1 adult female we assume spouses
    public int $_type;         // 0=patriarch, 1=martriarch

    // constructor, initialize variables
    public function __construct(int $famtype)
    {
        $this->_type = $famtype;
    }

    /** Add what we need to know about members for role assignment later **/
    public function addMember(int $PersonID, int $Gender, int $Age, string $Wedding = '', $Phone = '', $Envelope = 0): void
    {
        // add member with un-assigned role
        $this->Members[] = [
            'personid'     => $PersonID,
            'age'     => $Age,
            'gender'  => $Gender,
            'role'    => 0,
            'phone'   => $Phone,
            'envelope' => $Envelope,
        ];
        if ($Wedding !== '') {
            $this->WeddingDate = $Wedding;
        }
        if ($Envelope !== 0) {
            $this->Envelope = $Envelope;
        }
        $this->MemberCount++;
        if ($Age > 18) {
            $Gender === 1 ? $this->_nAdultMale++ : $this->_nAdultFemale++;
        }
    }

    /** Assigning of roles to be called after all members added **/
    public function assignRoles()
    {
        // only one member, must be "head"
        if ($this->MemberCount === 1) {
            $this->Members[0]['role'] = 1;
            $this->Phone = $this->Members[0]['phone'];
        } else {
            for ($m = 0; $m < $this->MemberCount; $m++) {
                if ($this->Members[$m]['age'] >= 0) { // -1 if unknown age
                    // child
                    if ($this->Members[$m]['age'] <= 18) {
                        $this->Members[$m]['role'] = 3;
                    } else {
                        // if one adult male and 1 adult female we assume spouses
                        if ($this->_nAdultMale === 1 && $this->_nAdultFemale === 1) {
                            // find head / spouse
                            if (($this->Members[$m]['gender'] === 1 && $this->_type === 0) || ($this->Members[$m]['gender'] === 2 && $this->_type === 1)) {
                                $this->Members[$m]['role'] = 1;
                                if ($this->Members[$m]['phone'] != '') {
                                    $this->Phone = $this->Members[$m]['phone'];
                                }
                            } else {
                                $this->Members[$m]['role'] = 2;
                            }
                        }
                    }
                }
            }
        }
    }
}

$sPageTitle = gettext('CSV Import');
require_once 'Include/Header.php'; ?>

<div class="card">
<div class="card-header">
   <h3 class="card-title"><?= gettext('Import Data')?></h3>
</div>
<div class="card-body">

<?php

$iStage = 1;
$csvError = '';

// Is the CSV file being uploaded?
if (isset($_POST['UploadCSV'])) {
    // Check if a valid CSV file was actually uploaded
    if (empty($_FILES['CSVfile']['name'])) {
        $csvError = gettext('No file selected for upload.');
    } else {
        // Valid file, so save it and display the import mapping form.
        // Use a temp filename in the system temp dir, and save in SESSION
        $csvTempFile = tempnam(sys_get_temp_dir(), 'csvimport');
        $_SESSION['csvTempFile'] = $csvTempFile;
        move_uploaded_file($_FILES['CSVfile']['tmp_name'], $csvTempFile);

        // create the file pointer
        $pFile = fopen($csvTempFile, 'r');

        // count # lines in the file
        $iNumRows = 0;
        while ($tmp = fgets($pFile, 2048)) {
            $iNumRows++;
        }
        rewind($pFile);

        // create the form?>
        <form method="post" action="CSVImport.php">

        <?php
        echo gettext('Total number of rows in the CSV file:') . $iNumRows;
        echo '<br><br>';
        echo '<table class="table horizontal-scroll" id="importTable">';

        // grab and display up to the first 8 lines of data in the CSV in a table
        $iRow = 0;
        while (($aData = fgetcsv($pFile, 2048, ',')) && $iRow++ < 9) {
            $numCol = count($aData);

            echo '<tr>';
            for ($col = 0; $col < $numCol; $col++) {
                echo '<td>' . htmlspecialchars($aData[$col]) . '&nbsp;</td>';
            }
            echo '</tr>';
        }

        fclose($pFile);

        $sSQL = 'SELECT custom_Field, custom_Name, type_ID FROM person_custom_master ORDER BY custom_Order';
        $rsCustomFields = RunQuery($sSQL);

        $sPerCustomFieldList = '';
        while ($aRow = mysqli_fetch_array($rsCustomFields)) {
            $sPerCustomFieldList .= '<option value="' . $aRow['custom_Field'] . '">' . $aRow['custom_Name'] . "</option>";
        }

        $sSQL = 'SELECT fam_custom_Field, fam_custom_Name, type_ID FROM family_custom_master ORDER BY fam_custom_Order';
        $rsfamCustomFields = RunQuery($sSQL);

        $sFamCustomFieldList = '';
        while ($aRow = mysqli_fetch_array($rsfamCustomFields)) {
            if (!in_array($aRow['type_ID'], [9, 12])) {
                $sFamCustomFieldList .= '<option value="f' . $aRow['fam_custom_Field'] . '">' . $aRow['fam_custom_Name'] . "</option>";
            }
        }

        // Get Field Security List Matrix
        $sSQL = 'SELECT lst_OptionID, lst_OptionName FROM list_lst WHERE lst_ID = 5 ORDER BY lst_OptionSequence';
        $rsSecurityGrp = RunQuery($sSQL);

        while ($aRow = mysqli_fetch_array($rsSecurityGrp)) {
            $aSecurityType[$aRow['lst_OptionID']] = $aRow['lst_OptionName'];
        }

        // add select boxes for import destination mapping
        // and provide with unique id to assist with testing
        for ($col = 0; $col < $numCol; $col++) {
            // debug_log("Number of columns detected: " . $numCol);
            // debug_log("Column mappings: " . print_r($_POST, true));
            ?>
            <td>
            <select id="<?= 'SelField' . $col ?>" name="<?= 'col' . $col ?>" class="columns">
                <option value="0"><?= gettext('Ignore this Field') ?></option>
                <option value="1"><?= gettext('Title') ?></option>
                <option value="2"><?= gettext('First Name') ?></option>
                <option value="3"><?= gettext('Middle Name') ?></option>
                <option value="4"><?= gettext('Last Name') ?></option>
                <option value="5"><?= gettext('Suffix') ?></option>
                <option value="6"><?= gettext('Gender') ?></option>
                <option value="7"><?= gettext('Donation Envelope') ?></option>
                <option value="8"><?= gettext('Address') ?> 1</option>
                <option value="9"><?= gettext('Address') ?> 2</option>
                <option value="10"><?= gettext('City') ?></option>
                <option value="11"><?= gettext('State') ?></option>
                <option value="12"><?= gettext('Zip') ?></option>
                <option value="13"><?= gettext('Country') ?></option>
                <option value="14"><?= gettext('Home Phone') ?></option>
                <option value="15"><?= gettext('Work Phone') ?></option>
                <option value="16"><?= gettext('Mobile Phone') ?></option>
                <option value="17"><?= gettext('Email') ?></option>
                <option value="18"><?= gettext('Work / Other Email') ?></option>
                <option value="19"><?= gettext('Birth Date') ?></option>
                <option value="20"><?= gettext('Membership Date') ?></option>
                <option value="21"><?= gettext('Wedding Date') ?></option>
                <?= $sPerCustomFieldList . $sFamCustomFieldList ?>
            </select>
            </td>
            <?php
        }

        echo '</table>'; ?>
        <BR>
        <input type="checkbox" value="1" name="IgnoreFirstRow"><?= gettext('Ignore first CSV row (to exclude a header)') ?>
        <BR><BR>
        <BR>
        <input type="checkbox" value="1" name="MakeFamilyRecords" checked="true">
        <select name="MakeFamilyRecordsMode">
            <option value="0"><?= gettext('Make Family records based on last name and address') ?></option>
            <?= $sPerCustomFieldList . $sFamCustomFieldList ?>
        </select>

        <BR><BR>
        <select name="FamilyMode">
            <option value="0"><?= gettext('Patriarch') ?></option>
            <option value="1"><?= gettext('Matriarch') ?></option>
        </select>
        <?= gettext('Family Type: used with Make Family records... option above') ?>
        <BR><BR>
        <select name="DateMode">
            <option value="1">YYYY-MM-DD</option>
            <option value="2">MM-DD-YYYY</option>
            <option value="3">DD-MM-YYYY</option>
        </select>
        <?= gettext('NOTE: Separators (dashes, etc.) or lack thereof do not matter') ?>
        <BR><BR>
        <?php
            $sCountry = SystemConfig::getValue('sDefaultCountry');
        require_once 'Include/CountryDropDown.php';
        echo gettext('Default country if none specified otherwise');

        $sSQL = 'SELECT lst_OptionID, lst_OptionName FROM list_lst WHERE lst_ID = 1 ORDER BY lst_OptionSequence';
        $rsClassifications = RunQuery($sSQL); ?>
        <BR><BR>
        <select name="Classification">
            <option value="0"><?= gettext('Unassigned') ?></option>
            <option value="" disabled>-----------------------</option>

            <?php
            while ($aRow = mysqli_fetch_array($rsClassifications)) {
                echo '<option value="' . $aRow['lst_OptionID'] . '"';
                echo '>' . $aRow['lst_OptionName'] . '&nbsp;';
            } ?>
        </select>
        <?= gettext('Classification') ?>
        <BR><BR>
        <input id="DoImportBtn" type="submit" class="btn btn-primary" value="<?= gettext('Perform Import') ?>" name="DoImport">
        </form>

        <?php
        $iStage = 2;
    }
}

// Has the import form been submitted yet?
if (isset($_POST['DoImport'])) {
    // debug_log("DoImport form submitted");
    // debug_log("POST data: " . print_r($_POST, true));
    $aColumnCustom = [];
    $aFamColumnCustom = [];
    $bHasCustom = false;
    $bHasFamCustom = false;

    //Get the temp filename stored in the session
    $csvTempFile = $_SESSION['csvTempFile'];

    $Families = [];
    // make sure the file still exists
    if (is_file($csvTempFile)) {
        // debug_log("CSV temp file exists: " . $csvTempFile);
        // debug_log("File size: " . filesize($csvTempFile) . " bytes");
        // create the file pointer
        $pFile = fopen($csvTempFile, 'r');

        $bHasCustom = false;
        $sDefaultCountry = InputUtils::legacyFilterInput($_POST['Country']);
        $iClassID = InputUtils::legacyFilterInput($_POST['Classification'], 'int');
        $iDateMode = InputUtils::legacyFilterInput($_POST['DateMode'], 'int');

        // Get the number of CSV columns for future reference
        $aData = fgetcsv($pFile, 2048, ',');
        $numCol = count($aData);
        if (!isset($_POST['IgnoreFirstRow'])) {
            rewind($pFile);
        }

        // Put the column types from the mapping form into an array
        for ($col = 0; $col < $numCol; $col++) {
            if (mb_substr($_POST['col' . $col], 0, 1) === 'c') {
                $aColumnCustom[$col] = 1;
                $aFamColumnCustom[$col] = 0;
                $bHasCustom = true;
            } else {
                $aColumnCustom[$col] = 0;
                if (mb_substr($_POST['col' . $col], 0, 2) === 'fc') {
                    $aFamColumnCustom[$col] = 1;
                    $bHasFamCustom = true;
                } else {
                    $aFamColumnCustom[$col] = 0;
                }
            }
            $aColumnID[$col] = $_POST['col' . $col];
        }

        if ($bHasCustom) {
            // Get custom field definitions with their list references
            $sSQL = 'SELECT custom_Field, type_ID, custom_Special FROM person_custom_master';
            $rsCustomFields = RunQuery($sSQL);

            while ($aRow = mysqli_fetch_array($rsCustomFields)) {
                $aCustomTypes[$aRow['custom_Field']] = $aRow['type_ID'];
                
                // Load dropdown options for type 9 (person-from-group) and 12 (custom-list)
                if (in_array($aRow['type_ID'], [9, 12])) {
                    // For dropdown fields, the list ID should be in custom_Special field
                    // or we need to derive it from the field configuration
                    $listId = null;
                    
                    // Method 1: Check if custom_Special contains the list ID
                    if (!empty($aRow['custom_Special']) && is_numeric($aRow['custom_Special'])) {
                        $listId = (int)$aRow['custom_Special'];
                    } else {
                        // Method 2: Try to find the list ID by looking for a pattern
                        // This might need adjustment based on your specific database structure
                        
                        // Skip this field if we can't determine the list ID
                        debug_log("Warning: Could not determine list ID for custom field: " . $aRow['custom_Field']);
                        continue;
                    }
                    
                    if ($listId) {
                        $sSQL2 = "SELECT lst_OptionID, lst_OptionName FROM list_lst WHERE lst_ID = $listId ORDER BY lst_OptionSequence";
                        $rsListOptions = RunQuery($sSQL2);
                        
                        // Initialize array for this field
                        $aCustomDropdownOptions[$aRow['custom_Field']] = [];
                        
                        while ($listRow = mysqli_fetch_array($rsListOptions)) {
                            $aCustomDropdownOptions[$aRow['custom_Field']][$listRow['lst_OptionName']] = $listRow['lst_OptionID'];
                        }
                        
                        // debug_log("Loaded " . count($aCustomDropdownOptions[$aRow['custom_Field']]) . " dropdown options for field: " . $aRow['custom_Field']);
                    }
                }
            }

            $sSQL = 'SELECT fam_custom_Field, type_ID FROM family_custom_master';
            $rsfamCustomFields = RunQuery($sSQL);

            while ($aRow = mysqli_fetch_array($rsfamCustomFields)) {
                $afamCustomTypes[$aRow['fam_custom_Field']] = $aRow['type_ID'];
            }
        }

        //
        // Need to lock the person_custom and person_per tables!!
        //

        $aPersonTableFields = [
                1 => 'per_Title', 2 => 'per_FirstName', 3 => 'per_MiddleName', 4 => 'per_LastName',
                5 => 'per_Suffix', 6 => 'per_Gender', 7 => 'per_Envelope', 8 => 'per_Address1', 9 => 'per_Address2',
                10 => 'per_City', 11 => 'per_State', 12 => 'per_Zip', 13 => 'per_Country', 14 => 'per_HomePhone',
                15 => 'per_WorkPhone', 16 => 'per_CellPhone', 17 => 'per_Email', 18 => 'per_WorkEmail',
                19 => 'per_BirthYear, per_BirthMonth, per_BirthDay', 20 => 'per_MembershipDate',
                21 => 'fam_WeddingDate',
        ];

        $importCount = 0;
        // debug_log("Starting data import loop");

        while ($aData = fgetcsv($pFile, 2048, ',')) {
            // debug_log("Processing CSV row: " . print_r($aData, true));
            // debug_log("Row data count: " . count($aData));

            // Validate row data
            if (empty($aData) || count($aData) < 2) {
                // debug_log("SKIPPING: Invalid or empty row");
                continue;
            }
            $iBirthYear = 0;
            $iBirthMonth = 0;
            $iBirthDay = 0;
            $iGender = 0;
            $dWedding = '';
            $sAddress1 = '';
            $sAddress2 = '';
            $sCity = '';
            $sState = '';
            $sZip = '';
            // Use the default country from the mapping form in case we don't find one otherwise
            $sCountry = SystemConfig::getValue('sDefaultCountry');
            $iEnvelope = 0;

            $sSQLpersonFields = 'INSERT INTO person_per (';
            $sSQLpersonData = ' VALUES (';
            $sSQLcustom = 'UPDATE person_custom SET ';
            // debug_log("Initial SQL setup complete");

            // Build the person_per SQL first.
            // We do this in case we can get a country, which will allow phone number parsing later
            $sFirstName = '';
            $sLastName = '';
            for ($col = 0; $col < $numCol; $col++) {
                // Is it not a custom field?
                if (!$aColumnCustom[$col] && !$aFamColumnCustom[$col]) {
                    $currentType = $aColumnID[$col];

                    // handler for each of the 20 person_per table column possibilities
                    switch ($currentType) {    
                        // Address goes with family record if creating families
                        case 8:
                        case 9:
                        case 10:
                        case 11:
                        case 12:
                            // if not making family records, add to person
                            if (!isset($_POST['MakeFamilyRecords'])) {
                                $sSQLpersonData .= "'" . addslashes($aData[$col]) . "',";
                            } else {
                                switch ($currentType) {
                                    case 8:
                                        $sAddress1 = addslashes($aData[$col]);
                                        break;
                                    case 9:
                                        $sAddress2 = addslashes($aData[$col]);
                                        break;
                                    case 10:
                                        $sCity = addslashes($aData[$col]);
                                        break;
                                    case 11:
                                        $sState = addslashes($aData[$col]);
                                        break;
                                    case 12:
                                        $sZip = addslashes($aData[$col]);
                                }
                            }
                            break;

                        // Simple strings.. no special processing
                        case 1:
                        case 2: // First Name
                            $sFirstName = trim($aData[$col]);
                            $sSQLpersonData .= "'" . addslashes($aData[$col]) . "',";
                            break;
                        case 3:
                        case 4: // Last Name  
                            $sLastName = trim($aData[$col]);
                            $sSQLpersonData .= "'" . addslashes($aData[$col]) . "',";
                            break;
                        case 5:
                        case 17:
                        case 18:
                                        $sSQLpersonData .= "'" . addslashes($aData[$col]) . "',";
                            break;

                        // Country.. also set $sCountry for use later!
                        case 13:
                            $sCountry = $aData[$col];
                            break;

                        // Gender.. check for multiple possible designations from input
                        case 6:
                            switch (strtolower($aData[$col])) {
                                case 'male':
                                case 'm':
                                case 'boy':
                                case 'man':
                                            $sSQLpersonData .= '1, ';
                                            $iGender = 1;
                                    break;
                                case 'female':
                                case 'f':
                                case 'girl':
                                case 'woman':
                                            $sSQLpersonData .= '2, ';
                                            $iGender = 2;
                                    break;
                                default:
                                    $sSQLpersonData .= '0, ';
                                    break;
                            }
                            break;

                        // Donation envelope.. make sure it's available!
                        case 7:
                            $iEnv = InputUtils::legacyFilterInput($aData[$col], 'int');
                            if (empty($iEnv)) {
                                $iEnvelope = 0;
                            } else {
                                $sSQL = "SELECT '' FROM person_per WHERE per_Envelope = " . $iEnv;
                                $rsTemp = RunQuery($sSQL);
                                if (mysqli_num_rows($rsTemp) === 0) {
                                    $iEnvelope = $iEnv;
                                } else {
                                    $iEnvelope = 0;
                                }
                            }
                            break;

                        // Birth date.. parse multiple date standards.. then split into day,month,year
                        case 19:
                            $sDate = $aData[$col];
                            $aDate = ParseDate($sDate, $iDateMode);
                            $sSQLpersonData .= $aDate[0] . ',' . $aDate[1] . ',' . $aDate[2] . ',';
                            // Save these for role calculation
                            $iBirthYear = (int)$aDate[0];
                            $iBirthMonth = (int)$aDate[1];
                            $iBirthDay = (int)$aDate[2];
                            break;

                        // Membership date.. parse multiple date standards
                        case 20:
                            $sDate = $aData[$col];
                            $aDate = ParseDate($sDate, $iDateMode);
                            if (in_array('NULL', $aDate)) {
                                $sSQLpersonData .= 'NULL,';
                            } else {
                                $sSQLpersonData .= '"' . $aDate[0] . '-' . $aDate[1] . '-' . $aDate[2] . '",';
                            }
                            break;

                        // Wedding date.. parse multiple date standards
                        case 21:
                            $sDate = $aData[$col];
                            $aDate = ParseDate($sDate, $iDateMode);
                            if (in_array('NULL', $aDate)) {
                                $dWedding = 'NULL';
                            } else {
                                $dWedding = $aDate[0] . '-' . $aDate[1] . '-' . $aDate[2];
                            }
                            break;

                        // Ignore field option
                        case 0:
                        // Phone numbers.. uh oh.. don't know country yet.. wait to do a second pass!
                        case 14:
                        case 15:
                        case 16:
                        default:
                            break;
                    }

                    switch ($currentType) {
                        case 0:
                        case 7:
                        case 13:
                        case 14:
                        case 15:
                        case 16:
                        case 21:
                            break;
                        case 8:
                        case 9:
                        case 10:
                        case 11:
                        case 12:
                            // if not making family records, add to person
                            if (!isset($_POST['MakeFamilyRecords'])) {
                                $sSQLpersonFields .= $aPersonTableFields[$currentType] . ', ';
                            }
                            break;
                        default:
                            $sSQLpersonFields .= $aPersonTableFields[$currentType] . ', ';
                            break;
                    }
                }
            }
            // debug_log("First pass SQL fields: " . $sSQLpersonFields);
            // debug_log("First pass SQL data: " . $sSQLpersonData);

            // Second pass at the person_per SQL.. this time we know the Country
            for ($col = 0; $col < $numCol; $col++) {
                // Is it not a custom field?
                if (!$aColumnCustom[$col] && !$aFamColumnCustom[$col]) {
                    $currentType = $aColumnID[$col];
                    switch ($currentType) {
                        // Phone numbers..
                        case 14:
                        case 15:
                        case 16:
                                $sSQLpersonData .= "'" . addslashes(CollapsePhoneNumber($aData[$col], $sCountry)) . "',";
                                $sSQLpersonFields .= $aPersonTableFields[$currentType] . ', ';
                            break;
                        default:
                            break;
                    }
                }
            }
            // debug_log("Second pass completed");

            // Finish up the person_per SQL..
            $sSQLpersonData .= $iClassID . ",'" . addslashes($sCountry) . "',";
            $sSQLpersonData .= "'" . date('YmdHis') . "'," . AuthenticationManager::getCurrentUser()->getId();
            $sSQLpersonData .= ')';

            $sSQLpersonFields .= 'per_cls_ID, per_Country, per_DateEntered, per_EnteredBy';
            $sSQLpersonFields .= ')';
            $sSQLperson = $sSQLpersonFields . $sSQLpersonData;
            // debug_log("Final SQL query: " . $sSQLperson);

            RunQuery($sSQLperson);
            // $result = RunQuery($sSQLperson);
            // if (!$result) {
            //     debug_log("ERROR: SQL execution failed - " . mysqli_error($GLOBALS['cnInfoCentral']));
            //     debug_log("Failed SQL: " . $sSQLperson);
            //     continue; // Skip this record
            // } else {
            //     debug_log("SUCCESS: Person record inserted");
            // }

            // Make a one-person family if requested
            if (isset($_POST['MakeFamilyRecords'])) {
                $sSQL = 'SELECT MAX(per_ID) AS iPersonID FROM person_per';
                $rsPersonID = RunQuery($sSQL);
                $aRow = mysqli_fetch_array($rsPersonID);
                $iPersonID = $aRow['iPersonID'];
                $sSQL = 'SELECT * FROM person_per WHERE per_ID = ' . $iPersonID;
                $rsNewPerson = RunQuery($sSQL);
                extract(mysqli_fetch_array($rsNewPerson));

                // see if there is a family...
                if (!isset($_POST['MakeFamilyRecordsMode']) || $_POST['MakeFamilyRecordsMode'] == '0') {
                    // ...with same address only (more flexible family matching)
                    $sSQL = "SELECT fam_ID FROM family_fam WHERE fam_Address1 = '" . $sAddress1 . "'";
                    
                    // Optional: Add city and zip for more precise matching
                    if (!empty($sCity)) {
                        $sSQL .= " AND fam_City = '" . addslashes($sCity) . "'";
                    }
                    if (!empty($sZip)) {
                        $sSQL .= " AND fam_Zip = '" . addslashes($sZip) . "'";
                    }
                } else {
                    // ...with the same custom field values
                    $field = $_POST['MakeFamilyRecordsMode'];
                    $field_value = '';
                    for ($col = 0; $col < $numCol; $col++) {
                        if ($aFamColumnCustom[$col] && $field == $aColumnID[$col]) {
                            $field_value = trim($aData[$col]);
                            break;
                        }
                    }
                    $sSQL = 'SELECT f.fam_ID FROM family_fam f, family_custom c
                             WHERE f.fam_ID = c.fam_ID AND c.' . addslashes(mb_substr($field, 1)) . " = '" . addslashes($field_value) . "'";
                }
                // $logger->debug("Checking for existing family with: $sSQL");
                $rsExistingFamily = RunQuery($sSQL);
                $famid = 0;
                if (mysqli_num_rows($rsExistingFamily) > 0) {
                    $aRow = mysqli_fetch_array($rsExistingFamily);
                    $famid = $aRow['fam_ID'];
                    // $logger->debug("Found an existing family: with id=$famid");
                    if (array_key_exists($famid, $Families)) {
                        $Families[$famid]->addMember(
                            $per_ID,
                            $iGender,
                            GetAge($iBirthMonth, $iBirthDay, $iBirthYear),
                            $dWedding,
                            $per_HomePhone,
                            $iEnvelope
                        );
                    }
                } else {
                    // $logger->debug("No new family found -make one");
                    $family = new \ChurchCRM\model\ChurchCRM\Family();

                    // Create family name: "FirstName LastName" or just "LastName" if no first name
                    $familyName = '';
                    if (!empty($sFirstName) && !empty($sLastName)) {
                        $familyName = $sFirstName . ' ' . $sLastName;
                    } elseif (!empty($sLastName)) {
                        $familyName = $sLastName;
                    } elseif (!empty($sFirstName)) {
                        $familyName = $sFirstName;
                    } else {
                        $familyName = 'Unknown'; // fallback
                    }

                    $family
                        ->setName($familyName)  // FirstName + LastName
                        ->setAddress1($sAddress1)
                        ->setAddress2($sAddress2)
                        ->setCity($sCity)
                        ->setState($sState)
                        ->setZip($sZip)
                        ->setHomePhone($per_HomePhone)
                        ->setWorkPhone($per_WorkPhone)
                        ->setCellPhone($per_CellPhone)
                        ->setEmail($per_Email)
                        ->setDateEntered(date('YmdHis'))
                        ->setEnteredBy(AuthenticationManager::getCurrentUser()->getId());
                    $family->save();
                    // Call reload() to update our object fully
                    $family->reload();
                    // Now retrieve the ID just inserted
                    $famid = $family->getId();
                    // $logger->debug("The new family has id=$famid");

                    $note = new Note();
                    $note->setFamId($famid);
                    $note->setText(gettext('Imported'));
                    $note->setType('create');
                    $note->setEntered(AuthenticationManager::getCurrentUser()->getId());
                    $note->save();

                    $familyCustom = new FamilyCustom();
                    $familyCustom->setFamId($famid);
                    $familyCustom->save();

                    $fFamily = new Family(InputUtils::legacyFilterInput($_POST['FamilyMode'], 'int'));
                    // $logger->debug("Adding family member with per_id=$per_ID");
                    $fFamily->addMember(
                        $per_ID,
                        $iGender,
                        GetAge($iBirthMonth, $iBirthDay, $iBirthYear),
                        $dWedding,
                        $per_HomePhone,
                        $iEnvelope
                    );
                    $Families[$famid] = $fFamily;
                }

                $person = PersonQuery::create()->findOneById($per_ID);
                $person->setFamId($famid);
                $person->save();

                if ($bHasFamCustom) {
                    // Check if family_custom record exists
                    $sSQL = "SELECT fam_id FROM family_custom WHERE fam_id = $famid";
                    $rsFamCustomID = RunQuery($sSQL);
                    if (mysqli_num_rows($rsFamCustomID) === 0) {
                        $familyCustom = new FamilyCustom();
                        $familyCustom->setFamId($famid);
                        $familyCustom->save();
                    }

                    // Build the family_custom SQL
                    $sSQLFamCustom = 'UPDATE family_custom SET ';
                    for ($col = 0; $col < $numCol; $col++) {
                        // Is it a custom field?
                        if ($aFamColumnCustom[$col]) {
                            $colID = mb_substr($aColumnID[$col], 1);
                            $currentType = $afamCustomTypes[$colID];
                            $currentFieldData = trim($aData[$col]);

                            // If date, first parse it to the standard format..
                            if ($currentType === 2) {
                                $aDate = ParseDate($currentFieldData, $iDateMode);
                                if (in_array('NULL', $aDate)) {
                                    $currentFieldData = '';
                                } else {
                                    $currentFieldData = implode('-', $aDate);
                                }
                            } elseif ($currentType === 1) {
                                // If boolean, convert to the expected values for custom field
                                if (strlen($currentFieldData)) {
                                    $currentFieldData = ConvertToBoolean($currentFieldData);
                                }
                            } else {
                                $currentFieldData = addslashes($currentFieldData);
                            }

                            // aColumnID is the custom table column name
                            sqlCustomField($sSQLFamCustom, $currentType, $currentFieldData, $colID, $sCountry);
                        }
                    }

                    // Finalize and run the update for the person_custom table.
                    $sSQLFamCustom = mb_substr($sSQLFamCustom, 0, -2);
                    $sSQLFamCustom .= ' WHERE fam_ID = ' . $famid;
                    RunQuery($sSQLFamCustom);
                }
            }

            // Get the last inserted person ID and insert a dummy row in the person_custom table
            $sSQL = 'SELECT MAX(per_ID) AS iPersonID FROM person_per';
            $rsPersonID = RunQuery($sSQL);
            $aRow = mysqli_fetch_array($rsPersonID);
            $iPersonID = $aRow['iPersonID'];

            $note = new Note();
            $note->setPerId($iPersonID);
            $note->setText(gettext('Imported'));
            $note->setType('create');
            $note->setEntered(AuthenticationManager::getCurrentUser()->getId());
            $note->save();
            if ($bHasCustom) {
                $personCustom = new PersonCustom();
                $personCustom->setPerId($iPersonID);
                $personCustom->save();

                // Build the person_custom SQL
                for ($col = 0; $col < $numCol; $col++) {
                    // Is it a custom field?
                    if ($aColumnCustom[$col]) {
                        $currentType = $aCustomTypes[$aColumnID[$col]];
                        $currentFieldData = trim($aData[$col]);

                        if ($currentType === 2) {
                            // Date handling
                            $aDate = ParseDate($currentFieldData, $iDateMode);
                            if (in_array('NULL', $aDate)) {
                                $currentFieldData = '';
                            } else {
                                $currentFieldData = implode('-', $aDate);
                            }
                        } elseif ($currentType === 1) {
                            // Boolean handling
                            if (strlen($currentFieldData)) {
                                $currentFieldData = ConvertToBoolean($currentFieldData);
                            }
                        } elseif (in_array($currentType, [9, 12])) {
                            // Dropdown handling
                            $fieldName = $aColumnID[$col];
                            if (isset($aCustomDropdownOptions[$fieldName][$currentFieldData])) {
                                // Match found by option name
                                $currentFieldData = $aCustomDropdownOptions[$fieldName][$currentFieldData];
                            } elseif (is_numeric($currentFieldData) && in_array($currentFieldData, $aCustomDropdownOptions[$fieldName])) {
                                // Match found by option ID
                                $currentFieldData = (int)$currentFieldData;
                            } else {
                                // No match found, set to empty or default
                                $currentFieldData = 'NULL';
                            }
                        } else {
                            $currentFieldData = addslashes($currentFieldData);
                        }

                        // Skip empty values for integer fields
                        if (in_array($currentType, [9, 12]) && ($currentFieldData === '' || $currentFieldData === 'NULL')) {
                            // Skip this field entirely if it's empty for dropdown/integer types
                            continue;
                        }
                        sqlCustomField($sSQLcustom, $currentType, $currentFieldData, $aColumnID[$col], $sCountry);
                        $hasCustomData = true;
                    }
                }

                // Only run the custom SQL if we have data to update
                if ($hasCustomData) {
                    // Finalize and run the update for the person_custom table.
                    $sSQLcustom = mb_substr($sSQLcustom, 0, -2);
                    $sSQLcustom .= ' WHERE per_ID = ' . $iPersonID;
                    // debug_log("Custom SQL query: " . $sSQLcustom);
                    RunQuery($sSQLcustom);
                }
            }

            $importCount++;
            // debug_log("Import count: " . $importCount);
        }

        fclose($pFile);
        // debug_log("=== Import loop completed ===");
        // debug_log("Total imported: " . $importCount);

        // delete the temp file
        unlink($csvTempFile);

        // role assignments from config
        $aDirRoleHead = explode(',', SystemConfig::getValue('sDirRoleHead'));
        $aDirRoleSpouse = explode(',', SystemConfig::getValue('sDirRoleSpouse'));
        $aDirRoleChild = explode(',', SystemConfig::getValue('sDirRoleChild'));

        // update roles now that we have complete family data.
        foreach ($Families as $fid => $family) {
            $family->assignRoles();
            foreach ($family->Members as $member) {
                switch ($member['role']) {
                    case 1:
                        $iRole = (int)$aDirRoleHead[0];
                        break;
                    case 2:
                        $iRole = (int)$aDirRoleSpouse[0];
                        break;
                    case 3:
                        $iRole = (int)$aDirRoleChild[0];
                        break;
                    default:
                        $iRole = 0;
                }

                $person = PersonQuery::create()->findOneById($member['personid']);
                $person->setFmrId($iRole);
                $person->save();
            }

            $familyModel = FamilyQuery::create()->findOneById($fid);
            if ($family->WeddingDate !== '') {
                $familyModel->setWeddingdate($family->WeddingDate);
            }
            if ($family->Phone !== '') {
                $familyModel->setHomePhone($family->Phone);
            }
            if ($family->Envelope !== 0) {
                $familyModel->setEnvelope($family->Envelope);
            }
            $familyModel->save();
        }

        $iStage = 3;
        // debug_log("=== CSV Import Debug Finished ===");
        // debug_log("Final stage: " . $iStage);
    } else {
        echo gettext('ERROR: the uploaded CSV file no longer exists!');
    }
}

if ($iStage === 1) {
    // Display the select file form?>
    <p style="color: red"> <?= $csvError ?></p>
        <form method="post" action="CSVImport.php" enctype="multipart/form-data">
            <input id="CSVFileChooser" class="icTinyButton" type="file" name="CSVfile">
            <p></p>
            <input id="UploadCSVBtn" type="submit" class="btn btn-success" value=" <?= gettext('Upload CSV File') ?> " name="UploadCSV">
        </form>
    </p>
    <?php
    echo $sClear;
}

if ($iStage === 3) {
    echo '<p class="MediumLargeText">' . gettext('Data import successful.') . ' ' . $importCount . ' ' . gettext('persons were imported') . '</p>';
}

// Returns a date array [year,month,day]
function ParseDate(string $sDate, int $iDateMode): array
{
    // debug_log("Parsing date: '$sDate' with mode: $iDateMode");
    
    $cSeparator = '';
    $sDate = trim($sDate);
    
    // Find separator
    for ($i = 0; $i < strlen($sDate); $i++) {
        if (is_numeric(mb_substr($sDate, $i, 1))) {
            continue;
        }
        $cSeparator = mb_substr($sDate, $i, 1);
        break;
    }
    
    $aDate[0] = '0000';  // year
    $aDate[1] = '00';    // month  
    $aDate[2] = '00';    // day

    switch ($iDateMode) {
        case 1: // YYYY-MM-DD
            if (!is_numeric($cSeparator)) {
                $sDate = str_replace($cSeparator, '', $sDate);
            }
            if (strlen($sDate) === 8) {
                $aDate[0] = mb_substr($sDate, 0, 4);
                $aDate[1] = mb_substr($sDate, 4, 2);
                $aDate[2] = mb_substr($sDate, 6, 2);
            }
            break;

        case 2: // MM-DD-YYYY
            if ($cSeparator != '') {
                $tmpDate = explode($cSeparator, $sDate);
                if (count($tmpDate) >= 3) {
                    $aDate[0] = strlen($tmpDate[2]) === 4 ? $tmpDate[2] : '0000';
                    $aDate[1] = strlen($tmpDate[0]) === 2 ? $tmpDate[0] : '0' . $tmpDate[0];
                    $aDate[2] = strlen($tmpDate[1]) === 2 ? $tmpDate[1] : '0' . $tmpDate[1];
                }
            } else {
                if (strlen($sDate) === 8) {
                    $aDate[0] = mb_substr($sDate, 4, 4);
                    $aDate[1] = mb_substr($sDate, 0, 2);
                    $aDate[2] = mb_substr($sDate, 2, 2);
                }
            }
            break;

        case 3: // DD-MM-YYYY
            if ($cSeparator != '') {
                $tmpDate = explode($cSeparator, $sDate);
                if (count($tmpDate) >= 3) {
                    $aDate[0] = strlen($tmpDate[2]) === 4 ? $tmpDate[2] : '0000';
                    $aDate[1] = strlen($tmpDate[1]) === 2 ? $tmpDate[1] : '0' . $tmpDate[1];
                    $aDate[2] = strlen($tmpDate[0]) === 2 ? $tmpDate[0] : '0' . $tmpDate[0];
                }
            } else {
                if (strlen($sDate) === 8) {
                    $aDate[0] = mb_substr($sDate, 4, 4);
                    $aDate[1] = mb_substr($sDate, 2, 2);
                    $aDate[2] = mb_substr($sDate, 0, 2);
                }
            }
            break;
    }
    
    // FIX THE BUG: Original code had wrong logic
    if ((int) $aDate[0] <= 0 || (int) $aDate[0] < 1900 || (int) $aDate[0] > date('Y')) {
        $aDate[0] = 'NULL';
    }
    if ((int) $aDate[1] <= 0 || (int) $aDate[1] > 12) {
        $aDate[1] = 'NULL';
    }
    if ((int) $aDate[2] <= 0 || (int) $aDate[2] > 31) {
        $aDate[2] = 'NULL';
    }

    // debug_log("Parsed date result: " . print_r($aDate, true));
    return $aDate;
}

function GetAge(int $Month, int $Day, ?int $Year): int
{
    if ($Year === null) {
        return -1;
    }
    if ($Year > 0) {
        if ($Year == date('Y')) {
            return 0;
        } elseif ($Year == date('Y') - 1) {
            $monthCount = 12 - $Month + (int) date('m');
            if ($Day > date('d')) {
                $monthCount--;
            }
            if ($monthCount >= 12) {
                return 1;
            } else {
                return 0;
            }
        } elseif ($Month > date('m') || ($Month == date('m') && $Day > date('d'))) {
            return  date('Y') - 1 - $Year;
        } else {
            return  date('Y') - $Year;
        }
    } else {
        return -1;
    }
}
?>
</div>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  $(document).ready(function(){
    $(".columns").select2();
  });
</script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/MemberView.js" ></script>
<?php
// if (file_exists($debug_file)) {
//     echo "<div style='margin-top: 20px; padding: 10px; background: #f0f0f0; border: 1px solid #ccc;'>";
//     echo "<h4>Debug Log:</h4>";
//     echo "<pre style='height: 300px; overflow-y: scroll; font-size: 12px;'>";
//     echo htmlspecialchars(file_get_contents($debug_file));
//     echo "</pre>";
//     echo "<p><a href='?clear_debug=1'>Clear Debug Log</a></p>";
//     echo "</div>";
// }

// // Clear debug log if requested
// if (isset($_GET['clear_debug'])) {
//     if (file_exists($debug_file)) {
//         unlink($debug_file);
//     }
//     header('Location: CSVImport.php');
//     exit;
// }
require_once 'Include/Footer.php';