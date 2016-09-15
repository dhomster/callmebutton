/**
 * 4PSA VoipNow - CallMeButton App
 *
 * This files contains the JavaScript functions used in the application
 *
 * @version 2.0.0
 * @license released under GNU General Public License
 * @copyright (c) 2012 4PSA. (www.4psa.com). All rights reserved.
 * @link http://wiki.4psa.com
 */

/**
 * Verifies the number introduced in the input field, initiates the call, shows the status of the call
 */

/* retains the previous status if the call */
var prevStatus;

/* retains the APIID returned by the MakeCall request */
var callID;

/* array with the callids of the terminated calls */
/* usefull because the ajax requests for these callids must be stopped */
var mustStop = new Array();

var warnIcon = '<span class="warning-icon"></span>';

/**
 * Fetches a language message
 * @param code - code of the message
 */
function getLangMsg(code) {
    if(typeof(msgArr[code]) != 'undefined') {
        return msgArr[code];
    }
    return code;
}

/**
 * Verifies if a string represents a number
 * @param number - the string to verify
 * @return true - the string represents a number
 * @return false - the string doesn't represent a number
 */
function isNumeric(number)
{
    for (var i = 0; i < number.length; i++) {
        if (isFigure(number.charAt(i)) == false) {
            return false;
        }
    }
    return true;
}

/**
 * Verifies if a character represents a figure
 * @param element - the character to verify
 * @return true - the character represents a figure
 * @return false - the character doesn't represent a figure
 */
function isFigure(element) {
    var number = parseInt(element);

    if (number !== NaN) {
        if (element >= 0 && element <= 9) {
            return true;
        }
    }
    return false;
}

/**
 * Creates an XMLHttpRequest object, based on the browser type
 * @return xmlHttp - the XMLHttpRequest object
 */
function getAJAXObject() {
    var xmlHttp;
    try
    {
        // Firefox, Opera 8.0+, Safari
        xmlHttp = new XMLHttpRequest();
    } catch (e) {
        // Internet Explorer
        try
        {
            xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try
            {
                xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {
                alert("Your browser does not support AJAX!");
                return false;
            }
        }
    }
    return xmlHttp;
}

/**
 * Verifies the phone number value introduced in the input field
 * @return true - the value is ok
 * @return false - the is no value introduced
 */
function verifyFieldValue() {
    var fieldValue = document.getElementById('phone_number').value;
    var check = true;
    if (fieldValue != "") {
        if (isNumeric(fieldValue)) {
            /* the value is a number (public number) */
        } else {
            /* the value is not a number */
            /* check if the value contains '+' */
            var plusIndex = fieldValue.lastIndexOf('+');
            if (plusIndex > -1) {
                /* the value contains '+' */
                if (plusIndex > 0) {
                    /* the value contains '+' in another position than the first one */
                    check = false;
                } else {
                    /* it contains + on the first position */
                    /* the rest of the value must be a number */
                    if (isNumeric(fieldValue.substring(1)) == false) {
                        /* the rest of the value is not a number */
                        check = false;
                    }
                }
            } else {
                /* the value dosn't contain '+' and is not a number*/
                /* check if the value has just one '*' */
                var starFIndex = fieldValue.indexOf('*');
                var starLIndex = fieldValue.lastIndexOf('*');

                if (starFIndex == starLIndex && starFIndex > 0) {
                    /* the value has just one '*' */
                    /* check if the '*' separates two numbers */
                    var firstNumber = fieldValue.substring(0, starFIndex -1);
                    var secondNumber = fieldValue.substring(starFIndex + 1);
                    if (isNumeric(firstNumber) == false || isNumeric(secondNumber) == false) {
                        /* '*' doesn't separate two numbers */
                        check = false;
                    }
                } else {
                    /* the value has more than one '*' */
                    check = false;
                }
            }
        }
    } else {
        /* the value is null */
        check = false;
    }
    if (check == false) {
        /* display an error message */
        document.getElementById('msg_warn').style.display = "";
        document.getElementById('msg_warn').innerHTML = warnIcon+getLangMsg('err_invalid_number');
        return false;
    } else {
        /* the number is ok */
        /* hide the error message */
        document.getElementById('msg_warn').style.display = "none";
        call(fieldValue);
    }
}

/**
 * AJAX to request.php, which initiates the call
 * @param phoneNumber - the number of the customer
 */
function call(phoneNumber) {
    var xmlHttp = getAJAXObject();

    // 4 - The object has been created, but not initialized (the open method has not been called).
    // 0 - All the data has been received.
    if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
        var connString = "request.php?number=" + phoneNumber;
        xmlHttp.onreadystatechange = startStatus;
        xmlHttp.open("GET", connString, true);
        xmlHttp.send(null);
    }
}

/**
 * Gets the response from the above AJAX and calls the function that gets the status of the call
 */
function startStatus() {

    if (this.readyState == 4 && this.status == 200) {
        var response=this.responseText;

        //this response is given by the request.php script when error occured
        if (response == '1') {
            document.getElementById('msg_warn').style.display = "";
            document.getElementById('msg_warn').innerHTML = warnIcon+getLangMsg('err_req_status');
        } else {
            if (response) {
                var resp = JSON.parse(response);
                getCallStatus(resp[0]);
            } else {
                console.log('response was empty');
            }
        }
    }
}

/**
 * Calls a PHP script request.php which returns the status of the call
 * It is called every 5 seconds
 * @param phonecallLink - the object retrieved by startStatus()
 */
function getCallStatus(phoneCallLink) {
    /* set the APIID returned in the response for the MakeCall request */
    callID = phoneCallLink.id;
    xmlHttp = getAJAXObject();

    if (mustStop.indexOf(callID) !== -1) {
        /* if the ajax must be stopped */
        deleteFromArray(callID, mustStop);
    } else {
        if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
            /* the call link is found here */
            var connString = phoneCallLink.links.self;

            xmlHttp.onreadystatechange = displayCallStatus;
            xmlHttp.open("GET", 'request.php?status=1&url='+connString, true);
            xmlHttp.send(null);
        }

        /* set the timer to 5 sec */
        /* call this function with the proper parameters */
        setTimeout(function(){getCallStatus(phoneCallLink);}, 5000);
    }
}

/**
 * Processes the response and shows the status of the call
 */
function displayCallStatus()
{
    /* if the result is ready and the http status is ok */
    if (this.readyState == 4 && this.status == 200) {

        var response=JSON.parse(this.responseText);

        if (response == '1' || response == null) {
            /* kamailio or/and asterisk is down */
            document.getElementById('msg_warn').style.display = "";
            document.getElementById('msg_warn').innerHTML = warnIcon+getLangMsg('err_req_status');
            return;
        }
        var json = JSON.parse(this.responseText);

        if (json.error !== undefined) {
            /* kamailio or/and asterisk is down */
            document.getElementById('msg_warn').style.display = "";
            document.getElementById('msg_warn').innerHTML = warnIcon+json.error.message;
            return;
        }

        var status = '';

        //entry is always an array and we're interested only on the first result
        if (json.entry[0] === undefined) {
            document.getElementById('msg_warn').style.display = "";
            document.getElementById('msg_warn').innerHTML = warnIcon+'Hung up.';

            return;
        }

        switch(parseInt(json.entry[0].phoneCallView[0].status)) {
            case 1: {
                status = 'Off hook.';
                break;
            }
            case 2: {
                status = 'Trying...';
                break;
            }
            case 3: {
                status = 'Ringing...';
                break;
            }
            case 4: {
                status = 'Other side is ringing...';
                break;
            }
            case 5: {
                status = 'On call.';
                break;
            }
            default: {
                status = 'Unknown status.';
            }
        }

        document.getElementById('msg_warn').style.display = "";
        document.getElementById('msg_warn').innerHTML = warnIcon+status;

    }
}

/**
 * Deletes an element from an Array
 * @param element - the element that must be deleted
 * @param vect - the Array
 * @return new_array - the new array with the element deleted
 */
function deleteFromArray(element, vect) {
    var index = getElementIndex(element, vect);
    var new_array = vect.splice(index,1);
    return new_array;
}

/**
 * Inserts an alement into an Array if the element is not already in the array
 * @param element - the element to insert
 * @param vect - the array
 */
function insertInArray(element, vect) {
    var index = getElementIndex(element, vect);
    if (index == -1) {
        vect.push(element);
    }
}

/**
 * Gets the index of a specified element from a vector
 * @param element - the element
 * @param array - the array
 * @return - the index of element in the vector if the element is in the vector, -1 if the element is not found in the vector
 */
function getElementIndex(element, array) {
    for (var i = 0; i < array.length; i++) {
        if (array[i] == element) {
            return i;
        }
    }
    return -1;
}