<?php
    // get the json sent from frontend
    $inData = getRequestInfo();

    //
    $searchResults = "";
    $searchCount = 0;
    $userId = $inData["userid"];
    $search = "%" . $inData["searchterm"] . "%";
    //

    // connect to data base
    $conn = new mysqli("localhost", "dbuser", getenv("SQL_PW"), "ContactManager");
    if ($conn->connect_error) {
        // if error, return it to front end
        returnWithError($conn->connect_error);
    } else {
        // creates a new sql statement to get all records for a contact for the given user that has a first name or last name that contains the search term
        $stmt = $conn->prepare ("SELECT * FROM contacts WHERE userid=? and (firstname like ? or lastname like ?) ORDER BY lastname, firstname");
        $stmt->bind_param("sss", $userId, $search, $search);
        $stmt->execute();

        $result = $stmt->get_result();
        
        // loop over all result rows, construct the results array 
        while ($row = $result->fetch_assoc()) {
            if ($searchCount > 0) {
                $searchResults .= ",";
            }
            $searchCount++;
            $searchResults .= '{"id": "' . $row["id"] . '", "firstname": "' . $row["firstname"] . '", "lastname": "' . $row["lastname"] . '", "phone": "' . $row["phone"] . '", "email": "' . $row["email"] . '", "description": "' . $row["description"] . '"}'; //id firstname lastname phone, email, descirption
        }
        
        // chcek if any were created
        if ($searchCount == 0) {
            returnWithError("No Records Found.");
        } else {
            returnWithInfo($searchResults);
        }

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
        sendResultInfoAsJson('{"status": "error", "message": "' . $err . '"}');
    }

    // function to return a success state
    function returnWithInfo($searchResults) {
        $retValue = '{"results":[' . $searchResults . '],"error":""}';
        sendResultInfoAsJson($retValue);
    }
?>