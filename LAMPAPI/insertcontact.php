<?php
    // get the json sent from frontend
    $inData = getRequestInfo();

    //
    $fName = $inData["firstname"];
    $lName = $inData["lastname"];
    $phone = $inData["phone"];
    $email = $inData["email"];
    $userId = $inData["userid"];
    $description = $inData["description"];
    //

    // connect to data base
    $conn = new mysqli("localhost", "dbuser", getenv("SQL_PW"), "ContactManager");
    if ($conn->connect_error) {
        // if error, return it to front end
        returnWithError($conn->connect_error);
    } else {
        // creates a new sql statement to insert the new contact into the database
        $stmt = $conn->prepare("INSERT INTO contacts (firstname, lastname, userid, phone, email, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $fName, $lName, $userId, $phone, $email, $description);
        $stmt->execute();


        if ($stmt->errno) {
            returnWithError($stmt->error);
        } else {
            returnWithSuccess($stmt->insert_id);
        }

        $stmt->close();
    }
    // close the data base connection
    $conn->close();
    
    // helper function to get the input from front end and decode the json to a named array
    function getRequestInfo() {
        return json_decode(file_get_contents('php://input'), true);
    }

    // helper function to return the given object back to the front end, specifying the type as json. it is assumed $obj is already a json string
    function sendResultInfoAsJson($obj) {
        header('Content-type: application/json');
        echo $obj;
    }

    // helper function to send back an error with the specified message
    function returnWithError($err) {
        sendResultInfoAsJson('{"status": "error", "message": "' . $err . '", "id":0}');
    }

    // function to return a success state
    function returnWithSuccess($obj) {
        sendResultInfoAsJson('{"status": "success", "message": "Creation succeeded.", "id":' . $obj . '}');
    }
?>