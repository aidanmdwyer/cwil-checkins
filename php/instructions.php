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
        <h1>Index</h1>
        <div id="indexBody"></div>
    </div>
    <main>
        <?php if(accountProperties("Home Page")) { ?>
            <h2>Home Page (Viewing Buildings)</h2>
            <p>
                Once logged in, you will see a table of buildings. Here, you can see the building name, which City Wide
                manager is responsible for it, the IC assigned to it, the days it gets cleaned, whether the crew has arrived
                today, and the time they checked in.
                <br><br>By default, the “Today Only” filter will be turned on, showing only buildings that are scheduled to get
                cleaned on the current day. You can uncheck this to see all buildings. You can also use the manager and
                IC filters to view only buildings that are assigned to a specific manager or contractor.
                <br><br>When more than 20 buildings exist for the current filter, a “Load All” button will appear. Click this to view
                the entire list of buildings for that filter.
            </p>
        <?php } ?>
    </main>
</div>
</body>

<script>
    const indexBody = document.getElementById("indexBody");
    const sections = [
        "Home Page (Viewing Buildings)",
        "2",
        "3",
        "4",
        "5",
        "6",
        "7",
        "8",
        "9",
        "10",
        "11"
    ];
    let ul = document.createElement("ul");
    sections.forEach(item => {
        let li = document.createElement("li");
        li.innerText = item;
        ul.appendChild(li);
    });
    indexBody.appendChild(ul);
</script>

<style>
    body {
        background-color: lightgrey;
        margin: 10px;
        padding: 0;
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
    #index h1 {
        margin: 0;
        padding: 0;
    }
    #indexBody {
        width: 100%;
    }
    #indexBody ul {
        margin: 0;
        padding: 0;
        column-count: 3;
    }
    #indexBody li {
        text-align: left;
        text-wrap: nowrap;
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
        font-family: "Times New Roman", times, serif;
        font-size: 1.2rem;
        margin: 0;
        padding: 0;
    }
</style>
</html>
