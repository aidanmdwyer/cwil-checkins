<?php
include 'login.php';
include 'accountProperties.php';

$accountType = $_SESSION['accountType'];

$selectedBuildingsImgPath = "/imgs/instructions/SelectedBuildings" . (accountProperties("Print QR") ? "Print" : "") . (accountProperties("Delete Buildings") ? "Delete" : "") . (accountProperties("Edit Buildings") ? "Edit" : "") . ".png";

function implodeCommas(array $items, string $finalSeparator): string {
    $count = count($items);
    if ($count === 0) return '';
    if ($count === 1) return $items[0];
    if ($count === 2) return $items[0] . ' ' . $finalSeparator . ' ' . $items[1];
    //3+ items
    return implode(', ', array_slice($items, 0, -1))
        . ', ' . $finalSeparator . ' '
        . end($items);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Instructions</title>
</head>
<body>
<div id="page">
    <header>
        <img src="../imgs/logo.png">
        <h1>Check-in App Instructions</h1>
        <p>Please refer to these instructions on how to use the City Wide Check-ins App.</p>
        <div id="index">
            <h3>*Quick Reference*</h3>
        </div>
    </header>
    <main>

        <?php if(accountProperties("Home Page")) { ?>
            <div id="Viewing Buildings" class="section column">
                <h2>Viewing Buildings</h2>
                <p>
                    On the home page you will see a table of buildings, including the following information for each one:
                </p>
                <ul>
                    <?php if(accountProperties("See Building Name")) { ?><li>The building name</li><?php } ?>
                    <?php if(accountProperties("See Manager")) { ?><li>Which City Wide manager is responsible for it</li><?php } ?>
                    <?php if(accountProperties("See IC")) { ?><li>The IC assigned to it</li><?php } ?>
                    <?php if(accountProperties("See Days")) { ?><li>The days it gets cleaned</li><?php } ?>
                    <?php if(accountProperties("See Check-in Status")) { ?><li>Whether the crew has checked in for the day</li><?php } ?>
                    <?php if(accountProperties("See Check-in Time")) { ?><li>The time the crew checked in</li><?php } ?>
                </ul>
                <p>
                    You can also filter the buildings via the filter bar at the top with the following options:
                </p>
                <ul>
                    <?php if(accountProperties("Filter Today Only")) { ?><li><strong>Today Only: </strong>show only buildings that are scheduled to be cleaned on the current day</li><?php } ?>
                    <?php if(accountProperties("Filter Manager")) { ?><li><strong>Filter Manager: </strong>show only buildings managed by a specific City Wide manager</li><?php } ?>
                    <?php if(accountProperties("Filter IC")) { ?><li><strong>Filter IC: </strong>show only buildings cleaned by a specific contractor</li><?php } ?>
                    <?php if(accountProperties("Search Building Name")) { ?><li><strong>Search: </strong>search buildings by name</li><?php } ?>
                    <?php if(accountProperties("Access Inactive Buildings")) { ?><li><strong>Show Inactive: </strong>switch to view buildings marked inactive</li><?php } ?>
                </ul>
                <?php if(accountProperties("Access Inactive Buildings")) { ?>
                    <p>
                        You can also use the export button to export data to a spreadsheet.
                    </p>
                <?php } ?>
                <p>
                    When the table contains a lot of buildings, only the first 20 will be loaded and a “Load All” button will appear below the table.
                    Click the button to see the rest of the buildings.
                </p>
            </div>
        <?php } ?>

        <div id="QR Check-in" class="section column">
            <h2>QR Check-in</h2>
            <p>
                Cleaning crews are responsible for checking in via one of our QR codes when they arrive at a job site.
                This code will be found in a janitors closet or somewhere else easily accessible by the crew.
            </p>
            <div class="imgRow">
                <img src="/imgs/instructions/QRSlip.png">
            </div>
            <p>
                In order to check in, one member of the cleaning crew will scan the code with their phone's camera app.
                Then, they will be taken to a confirmation page with a green checkmark indicating the check in was successful.
                If the confirmation page displays an error, the crew should reach out to their City Wide point of contact to make sure they get checked in.
            </p>
            <div class="imgRow">
                <img src="/imgs/instructions/CheckInSuccess.png">
                <img src="/imgs/instructions/CheckInFailure.png">
            </div>
        </div>

        <?php if(accountProperties("Toggle Check-ins")) { ?>
            <div id="Manual Check-in" class="section column">
                <h2>Manual Check-in</h2>
                <p>
                    If need be, you may also manually check in cleaning crews on the home page.
                    Just click on the red X (&#10060) for the building you'd like to mark checked in.
                    You can also uncheck buildings that are already checked in by clicking on the checkmark (&#9989).
                </p>
            </div>
        <?php } ?>

        <?php if(accountProperties("Add Building Page")) { ?>
            <div id="Adding New Buildings" class="section column">
                <h2>Adding New Buildings</h2>
                <p>
                    This system is not automatically synced with any of our other client management software.
                    When we get new buildings, contractors, or managers, they must be added to this system separately.
                </p>
                <p>
                    To add a new building to the system, click the “Add Building” button on the home page.
                    Enter a building name, select it's manager, and search for your desired contractor.
                    Select the days the building is scheduled to be cleaned, use the common case checkboxes for convenience if one of them applies.
                </p>
                <div class="imgRow">
                    <img src="/imgs/instructions/AddBuilding.png">
                </div>
            </div>
        <?php } ?>

        <?php if(accountProperties("Edit Buildings")) { ?>
            <div id="Editing Buildings" class="section column">
                <h2>Editing Buildings</h2>
                <p>
                    You can edit building information by clicking the pen symbol in the rightmost column of the table.
                    This will open a menu where you can change the buildings manager, IC, and the days it is cleaned.
                </p>
                <div class="imgRow">
                    <img src="/imgs/instructions/EditBuilding.png">
                </div>
                <?php if(accountProperties("Select Buildings")) {
                    $editAllOptions = [];
                    if(accountProperties("Toggle Check-ins")) $editAllOptions[] = "check/uncheck";
                    if(accountProperties("Access Inactive Buildings")) $editAllOptions[] = "activate/deactivate";
                    $editAllOptions[] = "change the manager and ic of";
                    ?>
                    <p>
                        You can edit multiple buildings at once by selecting them with the checkboxes in the leftmost column.
                        Here you can <?php echo implodeCommas($editAllOptions, "or"); ?> multiple buildings at a time.
                    </p>
                    <div class="imgRow">
                        <img src="/imgs/instructions/SelectAll.png">
                        <img src="<?php echo $selectedBuildingsImgPath; ?>">
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if(accountProperties("Delete Buildings")) { ?>
            <div id="Deleting Buildings" class="section column">
                <h2>Deleting Buildings</h2>
                <p>
                    You can edit delete a building by selecting it with the checkbox in the leftmost column and clicking "Delete Building".
                    This action cannot be undone. Any QR slips associated with the deleted building will no longer function unless another
                    building is created with the exact same name.
                </p>
            </div>
        <?php } ?>

        <?php if(accountProperties("Managers Page") || accountProperties("Contractors Page")) {
            $addOptions = [];
            if(accountProperties("Managers Page")) $addOptions[] = "manager";
            if(accountProperties("Contractors Page")) $addOptions[] = "contractor";
            ?>
            <div id="Adding New <?php echo
            implode('/', array_map(function($item) {
                return ucfirst($item) . 's';
            }, $addOptions));
            ?>" class="section column">
                <h2>Adding New <?php echo
                    implode('/', array_map(function($item) {
                        return ucfirst($item) . 's';
                    }, $addOptions));
                    ?></h2>
                <p>
                    To add a new <?php echo
                    implode('/', array_map(function($item) {
                        return $item;
                    }, $addOptions));
                    ?> to the system, click on the <?php echo
                    implode(' or ', array_map(function($item) {
                        return '"' . ucfirst($item) . 's"';
                    }, $addOptions));
                    ?> button on the home page.
                    Then type their name into the <?php echo
                    implode(' or ', array_map(function($item) {
                        return '"Add New ' . ucfirst($item) . '"';
                    }, $addOptions));
                    ?> box and click submit.
                </p>
                <div class="imgRow">
                    <?php if(accountProperties("Managers Page")) {?><img src="/imgs/instructions/AddNewManager.png"><?php }?>
                    <?php if(accountProperties("Contractors Page")) {?><img src="/imgs/instructions/AddNewContractor.png"><?php }?>
                </div>
                <p>
                    Adding a <?php echo
                    implode('/', array_map(function($item) {
                        return $item;
                    }, $addOptions));
                    ?> to the list allows you to assign buildings to them, but they will still need to make an account to access the system themselves.
                    To have them create an account, click on the button under "Account Link" to copy their account link, and send it to them.
                </p>
                <div class="imgRow">
                    <?php if(accountProperties("Managers Page")) {?><img src="/imgs/instructions/ManagerList.png"><?php }?>
                    <?php if(accountProperties("Contractors Page")) {?><img src="/imgs/instructions/ContractorList.png"><?php }?>
                </div>
                <p>
                    If a <?php echo
                    implode(' or ', array_map(function($item) {
                        return $item;
                    }, $addOptions));
                    ?> reaches out to have their password reset, click the button under “Password Reset Link” to copy their link, and send it to them.
                    They will have 24 hours to reset their password with this link.
                    If they fail to do this in 24 hours, click the button again and send them the new link.
                </p>
                <p>
                    You can also delete <?php echo
                    implode(' and ', array_map(function($item) {
                        return $item;
                    }, $addOptions));
                    ?> as long as there are no longer any buildings assigned to them.
                    Deleting a name will automatically delete the associated account, ensuring the user no longer has access to our system.
                </p>
            </div>
        <?php } ?>

        <?php if(accountProperties("Accounts Page")) { ?>
            <div id="Adding a New Admin Account" class="section column">
                <h2>Adding a New Admin Account</h2>
                <p>
                    To add a new account for an administrator, click on the "Accounts" button on the home page.
                    Then type their name into the "Create New Admin Account" box and click submit.
                    In order for them to activate and access their account, you must send them their password reset link, which you can copy with the button under "Password Rest Link" in the "All Accounts" table.
                </p>
                <div class="imgRow">
                    <img src="/imgs/instructions/AddNewAdmin.png">
                    <img src="/imgs/instructions/AllAccountsTable.png">
                </div>
            </div>
        <?php } ?>

        <?php if(accountProperties("Accounts Page")) { ?>
            <div id="Changing Account Permissions" class="section column">
                <h2>Changing Account Permissions</h2>
                <div class="imgRow">
                    <div class="column">
                        <p>
                            You can edit the permissions of individual accounts, or of an entire account type, in the "Accounts" page.
                            Doing so will limit which pages and what data the users can and cannot access on the app.
                        </p>
                        <p>
                            To edit an individual account's permissions, click on the pen symbol by their name in the "All Accounts" list.
                            Their permissions will appear on the screen, where you can check/uncheck whichever permissions they should/shouldn't have.
                        </p>
                        <p>
                            If you have given an account custom permissions but wish to restore their permissions to the defaults for their account type, click on "Reset to Defaults" in their permissions page.
                        </p>
                    </div>
                    <img src="/imgs/instructions/AccountIndividualPermissions.png">
                </div>
                <div class="imgRow">
                    <div>
                        <p>
                            You can also change the default permissions of an entire account type by using the dropdown menus in the "Account Permissions" box.
                            Select the account type you want to edit in the first dropdown, and select "Default Permissions" in the second dropdown.
                            When you change these permissions, any account of that type that doesn't have their own custom permissions set will have those permissions applied.
                        </p>
                    </div>
                    <img src="/imgs/instructions/AccountDefaultPermissions.png">
                </div>
            </div>
        <?php } ?>

        <?php if(accountProperties("Archives Page")) { ?>
            <div id="Archives" class="section column">
                <h2>Archives</h2>
                <p>
                    You are also able to access check-in archives of any previous day.
                    Click the “Archives” button on the home page and select a date to see the check-in records from that day.
                </p>
                <p class="note">
                    *Please note that the archives only show buildings that were scheduled to be cleaned on the selected day.
                </p>
                <div class="imgRow">
                    <img src="/imgs/instructions/Archives.png">
                </div>
                <p>
                    Instead of resetting the check-ins at midnight as the day switches over, this system resets at 3AM to account for late night services.
                    Every day at 3AM, all check-ins get reset and the records for the day are put into the archives.
                </p>
            </div>
        <?php } ?>
    </main>
</div>
</body>

<script>
    const index = document.getElementById("index");
    const sections = document.getElementsByClassName("section");
    let ul = document.createElement("ul");
    Array.from(sections).forEach(element => {
        const sectionName = element.id;
        let li = document.createElement("li");
        let link = document.createElement("a");
        link.href = "#" + encodeURIComponent(sectionName);
        link.innerText = sectionName;
        li.appendChild(link);
        ul.appendChild(li);
    });
    index.appendChild(ul);
</script>

<style>
    html {
        -webkit-print-color-adjust: exact;
        scroll-behavior: smooth;
    }
    body {
        background-color: lightgrey;
        margin: 10px;
        padding: 0;

        font-family: "Times New Roman", times, serif;
        font-size: 1.2rem;
    }
    @media print {
        body {
            margin: 0;
        }
        #index {
            display: none;
        }
        img {
            display: table;
        }
    }
    #page {
        background-color: white;
        width: 1100px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 50px;
        padding: 80px 30px;
        margin: 0 auto;
    }
    @media (max-width: 1100px) {
        #page {
            width: 95%;
        }
    }



    header {
        display: flex;
        flex-direction: column;
        gap: 15px;
        align-items: center;
        text-align: center;
        width: 75%;
    }
    header img {
        margin: 0 0 10px 0;
        padding: 0;
        width: 400px;
    }
    header h1 {
        margin: 0;
        padding: 0;
    }
    @media (max-width: 1100px) {
        header {
            width: 85%;
        }
    }



    #index {
        width: 100%;
        background-color: lightgray;
        border-radius: 6px;
    }
    #index ul {
        column-count: 2;
        padding: 0;
        margin: 10px;
    }
    #index li {
        list-style-position: inside;
        padding: 0;
        margin: 0 0 10px 0;
    }
    @media(max-width: 700px) {
        #index ul {
            column-count: 1;
        }
    }



    main {
        display: flex;
        flex-direction: column;
        gap: 50px;
        align-items: flex-start;
        width: 100%;
    }
    h1 {
        font-family: Tahoma, sans-serif;
        margin: 0 0 2rem 0;
        padding: 0;
    }
    h2 {
        font-family: Tahoma, sans-serif;
        margin: 0 0 1rem 0;
        padding: 6px 10px;
        border-radius: 3px;
        width: 100%;
        text-align: start;
        background-color: #2c2c24;
        color: white;
    }
    h3 {
        font-family: Tahoma, sans-serif;
        margin: 0.5rem;
    }
    p {
        margin: 0 0 10px 0;
        padding: 0;
    }
    p.note {
        font-size: 0.8em;
        color: red;
    }

    .column {
        display: flex;
        flex-direction: column;
        gap: 8px;
        align-items: flex-start;
    }
    .section {
        width: 100%;
    }
    .section ul {
        margin: 5px 0 15px 0;
    }
    .imgRow {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        width: 100%;
        justify-content: flex-start;
        align-items: flex-start;
        gap: 30px;
        margin: 15px 0 30px 0;
    }
    .imgRow > img {
        max-height: 450px;
        max-width: 45%;
        border: 2px solid #222222;
    }
    .imgRow > div {
        flex: 1;
    }
</style>
</html>
