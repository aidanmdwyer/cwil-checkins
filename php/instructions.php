<?php
include 'login.php';
include 'accountProperties.php';

$accountType = $_SESSION['accountType'];
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
        <p>Please refer to these instructions on how to use the City Wide Check-ins App as an <?php echo $accountType ?>.</p>
    </header>
    <div id="index">
        <h2>Index</h2>
        <div id="indexBody"></div>
    </div>
    <main>
        <?php if(accountProperties("Home Page")) { ?>
            <div id="Home Page (Viewing Buildings)" class="section">
                <h2>Home Page (Viewing Buildings)</h2>
                <p>
                    Once logged in, you will see a table of buildings. Here, you can see:
                </p>
                <ul>
                    <?php if(accountProperties("See Building Name")) { ?><li>the building name</li><?php } ?>
                    <?php if(accountProperties("See Manager")) { ?><li>which City Wide manager is responsible for it</li><?php } ?>
                    <?php if(accountProperties("See IC")) { ?><li>the IC assigned to it</li><?php } ?>
                    <?php if(accountProperties("See Days")) { ?><li>the days it gets cleaned</li><?php } ?>
                    <?php if(accountProperties("See Check-in Status")) { ?><li>whether the crew has for the day</li><?php } ?>
                    <?php if(accountProperties("See Check-in Time")) { ?><li>the time the crew checked in</li><?php } ?>
                </ul>
                <p>
                    <?php if(accountProperties("Filter Today Only")) { ?>
                        By default, the “Today Only” filter will be turned on, showing only buildings that are scheduled to get cleaned on
                        the current day. You can uncheck this to see all buildings regardless of the days they are cleaned.
                    <?php } ?>

                    <?php
                    $managerAndIC = [];
                    if(accountProperties("Filter Manager")) $managerAndIC[] = "manager";
                    if(accountProperties("Filter IC")) $managerAndIC[] = "IC";
                    if(!empty($managerAndIC)) {
                        ?>
                        You can use the <?php echo implode("and the ", $managerAndIC) ?> filter to view only buildings that are assigned to a specific <?php echo implode("or ", $managerAndIC) ?>.
                    <?php } ?>

                    <?php if(accountProperties("Search Building Name")) { ?>
                        You can also search by building name with the search bar.
                    <?php } ?>

                </p>
                <br>
                <p>
                    When the table contains a lot of buildings, only the first 20 will be loaded and a “Load All” button will appear below.
                    Click the button to see the rest of the buildings.
                </p>
            </div>
        <?php } ?>
    </main>
</div>
</body>

<script>
    const indexBody = document.getElementById("indexBody");
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
    indexBody.appendChild(ul);
</script>

<style>
    body {
        background-color: lightgrey;
        margin: 10px;
        padding: 0;

        font-family: "Times New Roman", times, serif;
        font-size: 1.2rem;
    }
    #page {
        background-color: white;
        width: 70%;
        min-width: 8.5in;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        padding: 50px 30px;
        margin: 0 auto;
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



    #index {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 15px;
        align-items: center;
        text-align: center;
    }
    #index h2 {
        margin: 0;
        padding: 0;
    }
    #indexBody {
        width: 75%;
    }
    #indexBody ul {
        margin: 0;
        padding: 0;
        column-count: 3;
        list-style-position: inside;
    }
    #indexBody li {
        text-align: center;
        text-wrap: nowrap;
        color:
    }



    main {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }
    h1 {
        font-family: Tahoma, sans-serif;
        margin: 0 0 2rem 0;
        padding: 0;
    }
    h2 {
        font-family: Tahoma, sans-serif;
        margin: 0 0 1rem 0;
        padding: 0;
    }
    h3 {
        font-family: Tahoma, sans-serif;
        margin: 0 0 1rem 0;
        padding: 0;
    }
    p {
        margin: 0;
        padding: 0;
    }
</style>
</html>
