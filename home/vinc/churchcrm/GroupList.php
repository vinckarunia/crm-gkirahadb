<?php

require_once 'Include/Config.php';
require_once 'Include/Functions.php';

$sPageTitle = gettext('Group Listing');
require_once 'Include/Header.php';

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\model\ChurchCRM\ListOptionQuery;
use ChurchCRM\Utils\InputUtils;

$rsGroupTypes = ListOptionQuery::create()->filterById('3')->find();

?>

<p>
<label>
<?= gettext("Show type of group:") ?>
<select id="table-filter" class="form-control input-sm">
<option value=""><?= gettext("All") ?></option>
<?php
  echo '<option>' . gettext("Unassigned") . '</option>';
foreach ($rsGroupTypes as $groupType) {
    echo '<option>' . InputUtils::legacyFilterInput($groupType->getOptionName()) . '</option>';
} ?>
</select>
</label>
<div id="assignAgeGroupWrapper" style="display:none;" class="mt-3 text-end">
    <hr>
    <button id="assignAgeGroupsBtn" class="btn btn-primary">
        <?= gettext('Auto Assign Group Members') ?>
    </button>
    <br><br>
    <p>
        Menambahkan <strong>ANGGOTA AKTIF</strong> ke dalam grup berdasarkan ketentuan LKKJ:
        <ul>
            <li><strong>Anak: </strong>Usia 0-15 tahun
            <li><strong>Pra Remaja: </strong>Usia 13-15 tahun
            <li><strong>Remaja: </strong>Usia 16-19 tahun
            <li><strong>Pemuda: </strong>Usia 20-30 tahun
            <li><strong>Dewasa Muda: </strong>Usia 31-39 tahun
            <li><strong>Dewasa Madya: </strong>Usia 40-49 tahun
            <li><strong>Dewasa Senior: </strong>Usia 50-59 tahun
            <li><strong>Lansia: </strong>Usia 60 tahun ke atas
        </ul>
        Jika ada perubahan lebih lanjut, dapat dilakukan secara manual untuk setiap grup. Diharapkan untuk melakukan update secara berkala.
    </p>
    <hr>
</div>
<div id="assignSundaySchoolWrapper" style="display:none;" class="mt-3 text-end">
    <hr>
    <button id="assignSundaySchoolBtn" class="btn btn-primary">
        <?= gettext('Auto Assign Sunday School Members') ?>
    </button>
    <br><br>
    <p>
        Menambahkan <strong>ANGGOTA AKTIF</strong> ke dalam kelas sekolah minggu berdasarkan <strong>USIA</strong>:
        <ul>
            <li><strong>Batita: </strong>Usia &lt;3 tahun
            <li><strong>Balita: </strong>Usia 3-5 tahun
            <li><strong>Kelas 1: </strong>Usia 6 tahun <i>(dan seterusnya berurutan hingga kelas 6)</i>
            <li><strong>Pra-Remaja 1: </strong>Usia 12 tahun
            <li><strong>Pra-Remaja 2: </strong>Usia 13 tahun
            <li><strong>Remaja: </strong>Usia 14-19 tahun
        </ul>
        Direkomendasikan untuk melakukan update anggota setiap kelas secara manual.
    </p>
    <hr>
</div>

<div class="card card-body">
<table class="table" id="groupsTable">
</table>
<?php
if (AuthenticationManager::getCurrentUser()->isManageGroupsEnabled()) {
    ?>

<br>
<form action="#" method="get" class="form">
    <label for="addNewGroup"><?= gettext('Add New Group') ?> :</label>
    <input class="form-control newGroup" name="groupName" id="groupName" style="width:100%">
    <br>
    <div class="text-right">
        <button type="button" class="btn btn-primary" id="addNewGroup"><?= gettext('Add New Group') ?></button>
    </div>
</form>
    <?php
}
?>
</div>

<script src="skin/js/GroupList.js"></script>

<?php
require_once 'Include/Footer.php';
