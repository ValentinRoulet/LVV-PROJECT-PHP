var dmpConnectInstance = new DMPConnect(appendLog, updateStateDisplay, handleError, wrapperInitialized, "9982", "localhost.icanopee.net");

// Called when the wrapper is initialized.
function wrapperInitialized(serverUrl)
{
    if (serverUrl)
        console.log("Wrapper initialized. Server url is '" + serverUrl + "'");
    else
        console.log("An error occurred during the wrapper initialization. ");
}

// ---------------------------------------------------------------------------------------------------------------------------------

var g_vitaleMonitoringWS;
var g_cpxMonitoringWS;
var g_virtualPrinterMonitoringWS;
var g_cpsData;

var g_hpList = []; // A set of HP used for the demo.

var g_vitalePatientDatas;
var g_eventCodes = [];
var g_documentContent = undefined;
var g_informants = [];
var g_treatingPhysicianId;          // Currently selected treating physician.
var g_addressSampleList = [];    // Address samples.
var g_telecomSampleList = [];    // Telecom samples.
var g_additionalAuthorList = [];    // Current list of additional authors.

var g_patients = [];

var g_documents = [];

const tooltipTexts = {
    session: 'Vous devez ouvrir une session',
    PcscReaders: 'Vous devez détecter les lecteurs',
    cpxCard: 'Vous devez détecter la carte CPX',
    cpxCardRead: 'Vous devez lire la carte CPX',
    vitaleCard: 'Vous devez détecter la carte vitale',
    vitaleCardRead: 'Vous devez lire la carte vitale',
    DmpConnector: 'Vous devez créer le connecteur DMP',
    certifiedIdentity: 'Vous devez certifier l\'identité',
};

// ---------------------------------------------------------------------------------------------------------------------------------

function readSingleFile(e)
{
    var file = e.target.files[0];

    appendLog('Loading file...');

    if (!file)
    {
        return;
    }

    var reader = new FileReader();

    reader.onload = function (e)
    {
        var content = e.target.result;

        // Display the file content loaded by the browser (in base 64).
        var p = document.createElement("p");
        p.innerHTML = content;
        document.getElementById('file-content').appendChild(p);

        // Example of received content:
        // "data:text/plain;base64,VHJ1YyhtdWNoZSkgZXh0cuptZW1lbnQgcGFzc2lvbm5hbnQuDQoNCg=="

        var contentParts = content.split(",");

        g_documentContent = contentParts[1];
    };

    // Get data in base 64.
    reader.readAsDataURL(file);
}

// ---------------------------------------------------------------------------------------------------------------------------------

function getFormTextFieldValue(input)
{
    if (!input || !input.value)
        return "";

    return input.value;
}

// Return 0 in case of error since for DmpConnect a null handle is an invalid one.
function getHandle(input)
{
    if (!input || !input.value)
        return 0;

    var val = parseInt(input.value, 10);

    if (isNaN(val))
        return 0;
    else
        return val;
}

function getCpsPinCode()
{
    return getFormTextFieldValue(document.getElementById('cpsPinCode'));
}

function getSelectedPracticeLocationIndice()
{
    var e = document.getElementById('listOfPracticeLocationIndex');

    return e.options[ e.selectedIndex ].value;
}

function getSelectedPracticeSettings()
{
    var e = document.getElementById('listOfPracticeSetting');

    return e.options[ e.selectedIndex ].value;
}


function getCpxReaderIndex()
{
    return document.getElementById('cpxReaderIndex').value;
}

function getVitaleReaderIndex()
{
    return document.getElementById('vitaleReaderIndex').value;
}

// Get currently selected vitale patient Data :
// getCurrentVitalePatientName
// getCurrentVitalePatientGiven
// getCurrentVitalePatientBirthdayYYMMDD
function getCurrentVitalePatientName()
{
    return document.getElementById('vitalePatientName').value;
}

function getCurrentVitalePatientBirthName()
{
    return document.getElementById('vitalePatientBirthName').value;
}

function getCurrentVitalePatientGiven()
{
    return document.getElementById('vitalePatientGiven').value;
}

function getCurrentVitalePatientRawBirthdayYYYYMMDD()
{
    var selector = document.getElementById('vitalePatientSelector');
    var selectedIndex = selector.selectedIndex;
    if (selectedIndex >= 0 && selectedIndex < g_vitalePatientDatas.length)
    {
        var patient = g_vitalePatientDatas[ selectedIndex ];

        return patient.s_rawBirthdayYYYYMMDD;
    }

    return "";
}

function getCurrentVitalePatientBirthdayYYMMDD()
{
    var bd = document.getElementById('vitalePatientBirthday').value;

    if (bd.length == 0)
    {
        return "";
    } else if (bd.length == 6)
    {
        // Input is already YYMMDD.
        return bd;
    } else if (bd.length == 8)
    {
        // Input is DDMMYYYY.
        var d = bd.substring(0, 2);
        var m = bd.substring(2, 4);
        var y = bd.substring(6, 8);

        return y + m + d;
    }
}

function getCpxReaderName()
{
    var e = document.getElementById('listOfCpxReaderNames');

    if (e.selectedIndex >= 0)
        return e.options[ e.selectedIndex ].value;
    else
        return "";
}

function getVitaleReaderName()
{
    var e = document.getElementById('listOfVitaleReaderNames');

    if (e.selectedIndex >= 0)
        return e.options[ e.selectedIndex ].value;
    else
        return "";
}

function getCpxReaderCheckingInterval()
{
    return document.getElementById('cpxReaderCheckingInterval').value;
}

function getVitaleReaderCheckingInterval()
{
    return document.getElementById('vitaleReaderCheckingInterval').value;
}

// ---------------------------------------------------------------------------------------------------------------------------------

function appendLog()
{
    console.log.apply(null, arguments);
}

// Default error handler. See dmpConnectInstance construction.
function handleError(error, command, timeoutInSeconds, answerCallback)
{
    console.error('Handled error', error);

    answerCallback({'error': error});
}

function cancelClick(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
}

function disableActions(elements, dataTag, disabling_action_chx, state) {
    elements.forEach((element) => {
        const required = element.dataset[dataTag];
        const title = (element.getAttribute('title') || '');
        const titlePart = ' ' + tooltipTexts[required];
        if (!disabling_action_chx.checked && state[required] !== true)
        {
            element.classList.add('disabled');
            element.addEventListener('click', cancelClick);
            if (title.indexOf(titlePart) === -1)
            {
                element.setAttribute('title', title + titlePart);
            }
        } else
        {
            element.dataset.tooltip = '';
            element.classList.remove('disabled');
            element.removeEventListener('click', cancelClick);
            element.setAttribute('title', title.replace(titlePart, ''));
        }
    });
}

function updateDisabledActions() {
    var disabling_action_chx = document.getElementById('disabling_action');
    const state = dmpConnectInstance.getState();

    const elements = document.querySelectorAll('*[data-required]');
    disableActions(elements, 'required', disabling_action_chx, state);

    const dmpElements = document.querySelectorAll('*[data-dmprequired]');
    disableActions(dmpElements, 'dmprequired', disabling_action_chx, {...state.dmp});
}

function updateStateDisplay()
{
    //comment adam
    //var div = document.getElementById('appState');
    //div.innerHTML = "Session id: \"" + dmpConnectInstance.getSessionId() + "\"";

    //updateDisabledActions();
}

function onPatientChange()
{
    // NirOD, birthDate, birthRank.
    var selector = document.getElementById('listOfTestIns');
    if (selector != undefined)
    {
        var patient = g_patients[ selector.selectedIndex ];

        setTextValue("TD00_nirOD_in", patient.nirOD);
        setTextValue("TD00_birthDate_in", patient.birthDate);
        setTextValue("TD00_birthRank_in", patient.birthRank);
        setTextValue("TD00_nirIndividu_in", "");
    }
}

function clearDemoPatientList()
{
    var selector = document.getElementById('listOfTestIns');

    if ((selector !== null) && (typeof selector !== 'undefined'))
    {
        while (selector.firstChild)
        {
            selector.removeChild(selector.firstChild);
        }
    }

}

function updateDemoPatientList()
{
    clearDemoPatientList();

    var selector = document.getElementById('listOfTestIns');
    if ((selector !== null) && (typeof selector !== 'undefined'))
    {
        for (var idPatient = 0; idPatient < g_patients.length; ++idPatient)
        {
            var curPatient = g_patients[ idPatient ];
            var displayName = curPatient.name + " " + curPatient.given;
            var opt = document.createElement("option");

            opt.value = idPatient;
            opt.text = displayName;

            selector.appendChild(opt);
        }
    }
}

function loadDemoPatients()
{
    g_patients =
            [
                {"name": "Desmaux", "given": "Paul", "nirOD": "255069999999934D", "birthDate": "19980101", "birthRank": 1}, // Paul Desmaux
                {"name": "Desmaux", "given": "Nathalie", "nirOD": "255069999999934D", "birthDate": "19550615", "birthRank": 1}, // Nathalie Desmaux
                {"name": "INSFAMILLEQUATRE", "given": "Jean-Hugues", "nirOD": "167112622170459T", "birthDate": "20010723", "birthRank": 1}, // INSFAMILLEQUATRE Jean-Hugues
                {"name": "INSFAMILLEQUATRE", "given": "Jumeauun", "nirOD": "167112622170459T", "birthDate": "19990115", "birthRank": 1}, // INSFAMILLEQUATRE Jumeauun
                {"name": "INSFAMILLEQUATRE", "given": "Jumeaudeux", "nirOD": "167112622170459T", "birthDate": "19990115", "birthRank": 2}, // INSFAMILLEQUATRE Jumeaudeux
                {"name": "CORSE", "given": "Figatellix", "nirOD": "188102B17295264T", "birthDate": "19881012", "birthRank": 1}         // CORSE Figatellix
            ];
}

function loadPhysicianList()
{
    g_hpList =
            [
                {
                    "s_hpName": "Médecin",
                    "s_hpGiven": "Géné",
                    "s_hpProfession": "10", /* Médecin */
                    "s_hpProfessionOid": "1.2.250.1.71.1.2.7", /* */
                    "s_hpSpecialty": "SM26", /* Qualifié en Médecine Générale */
                    "s_hpInternalId": "Agent007", /* Id relative to the current structure */
                },
                {
                    "s_hpName": "Médecin",
                    "s_hpGiven": "Cardio",
                    "s_hpProfession": "10", /* Médecin */
                    "s_hpProfessionOid": "1.2.250.1.71.1.2.7", /* */
                    "s_hpSpecialty": "SM04", /* Cardiologie et maladies vasculaires */
                    "s_hpInternalId": "Agent008", /* Id relative to the current structure */
                },
                {
                    "s_hpName": "Médecin",
                    "s_hpGiven": "Etudiant",
                    "s_hpProfession": "10", /* Médecin */
                    "s_hpProfessionOid": "1.2.250.1.71.1.2.7", /* */
                    "s_hpInternalId": "997001045", /* Student number */
                    "i_hpInternalIdType": DMPConnect.IdentifierType.IdentifierType_NationalIdentifierStudent


                }
            ]
}


function loadSampleAddresses()
{
    g_addressSampleList =
            [
                {
                    "i_type": 4, /* 'Lieu de travail' */
                    "s_country": "France",
                    "s_state": "Nouvelle Aquitaine",
                    "s_city": "Bordeaux",
                    "s_postalCode": "33000",
                    "s_houseNumber": "",
                    "s_houseNumberNumeric": "",
                    "s_streetName": "Amélie Raba-léon",
                    "i_streetNameType": 11, /* Place */
                    "s_additionalLocator": "Couloir B",
                    "s_unitId": "Tripode",
                    "s_postBox": "",
                    "s_precInct": ""
                },
                {
                    "i_type": 1, /* 'Domicile' */
                    "s_country": "FRANCE",
                    "s_city": "Paris",
                    "s_postalCode": "75005",
                    "s_streetName": "Chat qui pêche",
                    "i_streetNameType": 15, /* Rue */
                    "s_houseNumber": "1"
                }
            ];
}

function loadSampleTelecoms()
{
    g_telecomSampleList =
            [
                {
                    "i_type": 1, /* Phone */
                    "i_usage": 8, /* Mobile */
                    "s_value": "0612345678"
                },
                {
                    "i_type": 3, /* Mail */
                    "i_usage": 10, /* Unknown */
                    "s_value": "test@test.com"
                }
            ];

}

function appendDemoPatientsFromVitaleData(aVitaleData, aCardStatus)
{
    for (var i = 0; i < aVitaleData.length; ++i)
    {
        // NIR
        var nirOD = aVitaleData[i].s_nir;
        nirOD = nirOD.replace(/\s/g, '');   // Remove space
        if (aCardStatus == 4)              // Test card
            nirOD += "T";
        else if (aCardStatus == 5)         // Demo card
            nirOD += "D";

        // birthDate
        var birthDate = aVitaleData[i].s_birthday
        if (birthDate.length == 6)
        {
            // The date format is YYMMDD: it misses the century.
            // For theses examples, we force the century to be 19 if the quality of the patient is not 6 (child).
            // Please note that this is *wrong* in almost all cases!
            // To get a correct value, one can use hl_regularizeBirthDateIfNeeded(), and/or ask the
            //  user to check the date and change it, if needed.
            if (aVitaleData[i].i_quality == 6)
            {
                birthDate = "20" + birthDate;
            } else
            {
                birthDate = "19" + birthDate;
            }
        } else if (birthDate.length == 8)
        {
            // The format is DDMMYYYY: we just have to reorder the digits to get the date in "YYYYMMDD".
            var d = birthDate.substring(0, 2);
            var m = birthDate.substring(2, 4);
            var y = birthDate.substring(4, 8);
            birthDate = y + m + d;
        }

        // birthRank
        var birthRank = aVitaleData[i].i_birthRank;

        g_patients.push({"name": aVitaleData[i].s_name, "given": aVitaleData[i].s_given, "nirOD": nirOD, "birthDate": birthDate, "birthRank": birthRank});
    }
    updateDemoPatientList();
}

function initializePageState()
{
    updateStateDisplay();

    var fn = document.getElementById('filenameId')
    if (fn)
        fn.addEventListener('change', readSingleFile, false);


    var disabling_action_chx = document.getElementById('disabling_action')
    if (disabling_action_chx)
        disabling_action_chx.addEventListener('change', updateDisabledActions, false);


    var coll = document.getElementsByClassName("collapsible");
    var i;

    for (i = 0; i < coll.length; i++) {
        coll[i].addEventListener("click", function () {
            this.classList.toggle("inactive");
        });
    }

    loadDemoPatients();
    updateDemoPatientList();
    loadPhysicianList();
    loadSampleAddresses();
    loadSampleTelecoms();



    onPatientChange()
}

// Given a valid result of hl_readCpxCard, fills the <select> values of the list of practice locations.
function fillPracticeLocationList(aCpxReadResult)
{
    var plListItem = document.getElementById("listOfPracticeLocationIndex");
    while (plListItem.firstChild)
    {
        plListItem.removeChild(plListItem.firstChild);
    }

    for (var i = 0; i < aCpxReadResult.PracticeLocations.length; ++i)
    {
        var opt = document.createElement("option");
        opt.text = aCpxReadResult.PracticeLocations[i].s_practiceLocationName;
        opt.value = i;

        plListItem.appendChild(opt);
    }
}




function setTextValue(aFieldName, aValue)
{
    var item = document.getElementById(aFieldName);
    item.value = aValue;
}

function getTextValue(aFieldName)
{
    return document.getElementById(aFieldName).value;
}

function setSelectionValue(aFieldName, aValue)
{
    var item = document.getElementById(aFieldName);
    if (item != undefined)
        item.value = aValue;
}

function getSelectionValue(aFieldName)
{
    return document.getElementById(aFieldName).value;
}

function setCheckBoxState(aFieldName, doChecked)
{
    var item = document.getElementById(aFieldName);
    item.checked = doChecked;
}

function getCheckBoxState(aFieldName)
{
    return document.getElementById(aFieldName).checked;
}

function clearVitalePatientDataTextFields()
{
    setSelectionValue("vitalePatientName", "");
    setSelectionValue("vitalePatientBirthName", "");
    setSelectionValue("vitalePatientGiven", "");
    setSelectionValue("vitalePatientBirthday", "");
    setSelectionValue("vitalePatientBirthRank", "");
    setSelectionValue("vitalePatientNir", "");
    setSelectionValue("vitalePatientCertifiedNir", "");
    setSelectionValue("vitalePatientInsC", "");
    setSelectionValue("vitalePatientQuality", "");
}

function clearVitalePatientSelector()
{
    var patientSelector = document.getElementById("vitalePatientSelector")

    if (patientSelector != null)
    {
        // Patient selector: remove all options.
        while (patientSelector.firstChild)
        {
            patientSelector.removeChild(patientSelector.firstChild);
        }
    }
}

function clearVitalePatientData()
{
    // Clear global variable.
    g_vitalePatientDatas = {};

    // Clear fields.
    clearVitalePatientSelector();
    clearVitalePatientDataTextFields()
}

function initVitalePatientSelector()
{
    clearVitalePatientSelector();

    var patientSelector = document.getElementById("vitalePatientSelector")

    if (patientSelector != null)
    {
        for (var i = 0; i < g_vitalePatientDatas.length; ++i)
        {
            var opt = document.createElement("option");
            opt.text = g_vitalePatientDatas[i].s_name + " " + g_vitalePatientDatas[i].s_given;
            patientSelector.appendChild(opt);
        }
        onSelectVitalePatientData()
    }
}

function onSelectVitalePatientData()
{
    var selector = document.getElementById('vitalePatientSelector');
    var selectedIndex = selector.selectedIndex;
    if (selectedIndex >= 0 && selectedIndex < g_vitalePatientDatas.length)
    {
        var patient = g_vitalePatientDatas[ selectedIndex ];

        setSelectionValue("vitalePatientName", patient.s_name);
        setSelectionValue("vitalePatientBirthName", patient.s_birthname);
        setSelectionValue("vitalePatientGiven", patient.s_given);
        setSelectionValue("vitalePatientBirthday", patient.s_birthday);
        setSelectionValue("vitalePatientBirthRank", patient.i_birthRank);
        setSelectionValue("vitalePatientNir", patient.s_nir);
        setSelectionValue("vitalePatientCertifiedNir", patient.s_certifiedNir);
        setSelectionValue("vitalePatientInsC", patient.s_insC);
        setSelectionValue("vitalePatientQuality", patient.i_quality);

        // Set DmpPatient informations
        setSelectionValue("dmpPatientName", patient.s_name);
        setSelectionValue("dmpPatientGiven", patient.s_given);
        setSelectionValue("dmpPatientBirthName", patient.s_birthname);
        setSelectionValue("dmpPatientBirthDate", patient.s_birthdayYYYYMMDD);


        var dmpGenderSelector = document.getElementById("dmpPatientGender");
        var dmpSexSelector = document.getElementById("dmpPatientSex");

        if ((dmpGenderSelector == null))
            return;

        if ((dmpSexSelector == null))
            return;

        var nir = patient.s_nir;

        if (nir.charAt(0) == '1')
        {
            console.log("men");
            dmpGenderSelector.value = 2
            dmpSexSelector.value = 2
        } else if (nir.charAt(0) == '2')
        {
            console.log("women");
            dmpGenderSelector.value = 3
            dmpSexSelector.value = 3
        } else
        {
            dmpGenderSelector.value = 1
            dmpSexSelector.value = 1
        }
    } else
    {
        clearVitalePatientDataTextFields();
    }
}

function addAdditionalAuthor()
{
    var additionalAuthorName = document.getElementById("additionalAuthorName").value;
    var additionalAuthorGiven = document.getElementById("additionalAuthorGiven").value;
    var additionalAuthorProfessionCode = document.getElementById("additionalAuthorProfessionCode").value;
    var additionalAuthorProfessionOid = document.getElementById("additionalAuthorProfessionOid").value;
    var additionalAuthorSpecialityCode = document.getElementById("additionalAuthorSpecialityCode").value;
    var additionalAuthorInternaId = document.getElementById("additionalAuthorInternaId").value;

    if (additionalAuthorName.length == 0)
    {
        console.error("Name is empty.");
        return;
    }
    if (additionalAuthorGiven.length == 0)
    {
        console.error("Given is empty.");
        return;
    }
    if (additionalAuthorProfessionCode.length == 0)
    {
        console.error("Profession code is empty.");
        return;
    }
    if (additionalAuthorProfessionOid.length == 0)
    {
        console.error("Profession OID is empty.");
        return;
    }
    if (additionalAuthorInternaId.length == 0)
    {
        console.error("Internal ID is empty.");
        return;
    }

    var hp =
            {
                "s_hpName": additionalAuthorName,
                "s_hpGiven": additionalAuthorGiven,
                "s_hpProfession": additionalAuthorProfessionCode,
                "s_hpProfessionOid": additionalAuthorProfessionOid,
                "s_hpSpecialty": additionalAuthorSpecialityCode,
                "s_hpInternalId": additionalAuthorInternaId
            };

    g_additionalAuthorList.push(hp);
    updateAdditionalAuthorList();
}

function addAdditionalSample1()
{
    var hp =
            {
                "s_hpName": "Médecin",
                "s_hpGiven": "Géné",
                "s_hpProfession": "10", /* Médecin */
                "s_hpProfessionOid": "1.2.250.1.71.1.2.7", /* */
                "s_hpSpecialty": "SM26", /* Qualifié en Médecine Générale */
                "s_hpInternalId": "Agent007", /* Id relative to the current structure */
            };

    g_additionalAuthorList.push(hp);
    updateAdditionalAuthorList();
}

function addAdditionalSample2()
{
    var hp =
            {
                "s_hpName": "Médecin",
                "s_hpGiven": "Cardio",
                "s_hpProfession": "10", /* Médecin */
                "s_hpProfessionOid": "1.2.250.1.71.1.2.7", /* */
                "s_hpSpecialty": "SM04", /* Cardiologie et maladies vasculaires */
                "s_hpInternalId": "Agent008", /* Id relative to the current structure */
            };

    g_additionalAuthorList.push(hp);
    updateAdditionalAuthorList();
}

function addAdditionalSample3()
{
    var hp =
            {
                "s_hpName": "Médecin",
                "s_hpGiven": "Etudiant",
                "s_hpProfession": "10", /* Médecin */
                "s_hpProfessionOid": "1.2.250.1.71.1.2.7", /* */
                "s_hpInternalId": "997001045", /* Student number */
                "i_hpInternalIdType": DMPConnect.IdentifierType.IdentifierType_NationalIdentifierStudent
            };

    g_additionalAuthorList.push(hp);
    updateAdditionalAuthorList();
}

function removeAdditionalAuthor(index)
{
    g_additionalAuthorList.splice(index, 1);
    updateAdditionalAuthorList();
}

function updateAdditionalAuthorList()
{
    var table = document.getElementById("additionalAuthorListTable");
    while (table.rows.length > 1)
    {
        table.deleteRow(-1);
    }

    for (var i = 0; i < g_additionalAuthorList.length; ++i)
    {
        var curHp = g_additionalAuthorList[i];

        var name = curHp.s_hpName;
        var given = curHp.s_hpGiven;
        var prof = curHp.s_hpProfession;

        var row = table.insertRow(-1);
        row.setAttribute("style", "border: 1px solid gray;text-align:center;");
        var c1 = row.insertCell(0);
        c1.setAttribute("style", "border: 1px solid gray;");
        var c2 = row.insertCell(1);
        c2.setAttribute("style", "border: 1px solid gray;");
        var c3 = row.insertCell(2);
        c3.setAttribute("style", "border: 1px solid gray;");
        var c4 = row.insertCell(3);
        c4.setAttribute("style", "border: 1px solid gray;");

        var btn = document.createElement("input");
        btn.setAttribute("type", "button");
        btn.setAttribute("value", "X");
        btn.setAttribute("name", "" + i);
        btn.onclick = (function (entry) {
            removeAdditionalAuthor(this.name);
        });

        c1.innerHTML = name;
        c2.innerHTML = given;
        c3.innerHTML = prof;

        c4.appendChild(btn);
    }
}

function clearAdditionalAuthors()
{
    g_additionalAuthorList = [];
    updateAdditionalAuthorList();
}


function removeInformant(index)
{
    g_informants.splice(index, 1);
    updateInformantTable();
}

function informantTypeToString(anInformantType)
{
    if (anInformantType == 1)
    {
        return "Informant";
    } else if (anInformantType == 2)
    {
        return "Emergency contact";
    } else if (anInformantType == 3)
    {
        return "Trusted contact";
    } else if (anInformantType == 4)
    {
        return "Unknown";
    } else
    {
        return "<ERROR>";
    }
}

function updateInformantTable()
{
    var table = document.getElementById("informantListTable")
    while (table.rows.length > 1)
    {
        table.deleteRow(-1);
    }

    for (var i = 0; i < g_informants.length; ++i)
    {
        var name = g_informants[i].s_name;
        var given = g_informants[i].s_given;
        var type = g_informants[i].i_type;

        var row = table.insertRow(-1);
        row.setAttribute("style", "border: 1px solid gray;text-align:center;");
        var c1 = row.insertCell(0);
        c1.setAttribute("style", "border: 1px solid gray;");
        c1.innerHTML = name;
        var c2 = row.insertCell(1);
        c2.setAttribute("style", "border: 1px solid gray;");
        c2.innerHTML = given;
        var c3 = row.insertCell(2);
        c3.innerHTML = informantTypeToString(type);
        c3.setAttribute("style", "border: 1px solid gray;");
        // Remove btn.
        var c4 = row.insertCell(3);
        c4.setAttribute("style", "border: 1px solid gray;");
        var btn = document.createElement("input");
        btn.setAttribute("type", "button");
        btn.setAttribute("value", "X");
        btn.setAttribute("name", "" + i);
        btn.onclick = (function (entry) {
            console.log("erase element " + (this.name));
            removeInformant(this.name);
        });
        c4.appendChild(btn);
    }
}

function removeEventCode(index)
{
    g_eventCodes.splice(index, 1);
    console.log(g_eventCodes);
    updateEventCodeTable();
    console.log(g_eventCodes);
}

function updateEventCodeTable()
{
    var table = document.getElementById("eventCodeListTable");
    while (table.rows.length > 1)
    {
        table.deleteRow(-1);
    }

    for (var i = 0; i < g_eventCodes.length; ++i)
    {
        var curClass = g_eventCodes[i].s_classification;
        var curCode = g_eventCodes[i].s_code;
        var curDescription = g_eventCodes[i].s_description;

        // Data
        var row = table.insertRow(-1);
        row.setAttribute("style", "border: 1px solid gray;text-align:center;");
        var c1 = row.insertCell(0);
        c1.setAttribute("style", "border: 1px solid gray;");
        c1.innerHTML = curClass;
        var c2 = row.insertCell(1);
        c2.setAttribute("style", "border: 1px solid gray;");
        c2.innerHTML = curCode;
        var c3 = row.insertCell(2);
        c3.setAttribute("style", "border: 1px solid gray");
        c3.innerHTML = curDescription;

        // Remove btn
        var c4 = row.insertCell(3);
        var btn = document.createElement("input");
        btn.setAttribute("type", "button");
        btn.setAttribute("value", "X");
        btn.setAttribute("name", "" + i);
        btn.onclick = (function (entry) {
            console.log("erase element " + (this.name));
            removeEventCode(this.name);
        });
        c4.appendChild(btn);
    }
}

function addInformant(anInformantName = undefined,
        anInformantGiven = undefined,
        anInformantType = undefined,
        anInformantRelationType = undefined,
        anInformantAddressId = - 1,
        anInformantTelecomId = - 1)
{
    var name = (anInformantName === undefined) ? getTextValue("informantName") : anInformantName;
    var given = (anInformantGiven === undefined) ? getTextValue("informantGiven") : anInformantGiven;
    var type = (anInformantType === undefined) ? getTextValue("informantType") : anInformantType;
    var relationType = (anInformantRelationType === undefined) ? getTextValue("informantRelationType") : anInformantRelationType;
    var addressId = (anInformantAddressId === -1) ? parseInt(getSelectionValue("informantAddress"), 10) : anInformantAddressId;
    var telecomId = (anInformantTelecomId === -1) ? parseInt(getSelectionValue("informantTelecom"), 10) : anInformantTelecomId;

    if (addressId >= g_addressSampleList.length)
        addressId = -1;
    if (telecomId >= g_telecomSampleList.length)
        telecomId = -1;

    if ((name.length > 0) &&
            (given.length > 0) &&
            (type.length > 0))
    {
        var newValue =
                {
                    "s_name": name,
                    "s_given": given,
                    "i_type": parseInt(type, 10),
                    "i_relationType": parseInt(relationType, 10)
                };
        if (addressId != -1)
        {
            newValue.Addresses = [g_addressSampleList[addressId]];
        }
        if (telecomId != -1)
        {
            newValue.Telecoms = [g_telecomSampleList[telecomId]];
        }


        g_informants.push(newValue);
        updateInformantTable();
    } else
    {
        console.error("One of the Informant parameter is empty");
}
}

//
function addEventCode(anEventClassif = undefined, anEventCode = undefined, anEventDescription = undefined)
{
    var eventCodeClassif = (anEventClassif === undefined) ? getSelectionValue("EventCodeClassification") : anEventClassif;
    var eventCodeCode = (anEventCode === undefined) ? getTextValue("eventCodeCode") : anEventCode;
    var eventCodeDescription = (anEventDescription === undefined) ? getTextValue("eventCodeDescription") : anEventDescription;

    if ((eventCodeClassif.length > 0) && (eventCodeCode.length > 0) && (eventCodeDescription.length > 0))
    {
        var newValue =
                {
                    "s_classification": eventCodeClassif,
                    "s_code": eventCodeCode,
                    "s_description": eventCodeDescription
                };
        g_eventCodes.push(newValue);
        updateEventCodeTable();
    } else
    {
        console.error("One of the Event code parameter is empty");
}
}

function addCCAMSampleEventCode()
{
    addEventCode("CCAM", "ACHA002", "Biopsie de lésion intracrânienne, par craniotomie");
}

function addCIM10SampleEventCode()
{
    addEventCode("CIM_10", "H540", "Cécité binoculaire");
}

function addDRCSampleEventCode()
{
    addEventCode("DRC", "", "");
}

function addLOINCSampleEventCode()
{
    addEventCode("LOINC", "26435-8", "Génétique humaine");
}

function addSNOMED35VFEventCode()
{
    addEventCode("SNOMED_3_5_VF", "DA-48034", "nystagmus");
}

function automaticDocumentCategoryBasedOnFormat()
{
    var format = getSelectionValue("documentFormat");
    var selectCat = document.getElementById("documentCategory");

    if (format === "VSM")
    {
        // Select SYNTH in document category.
        selectCat.value = "SYNTH";
    } else if (format === "CrBio")
    {
        // Select 11502-2 in document category.
        selectCat.value = "11502-2";
    } else if (format === "DLU")
    {
        // Select 34133-9 in document category.
        selectCat.value = "34133-9";
    }
}

function getCurrentDocumentFormat()
{
    var documentFormat;
    var documentFormatString = getSelectionValue("documentFormat");
    if (documentFormatString === "JPEG")
        documentFormat = DMPConnect.DocumentFormat.JpgImage;
    else if (documentFormatString === "PDF")
        documentFormat = DMPConnect.DocumentFormat.PdfApplication;
    else if (documentFormatString === "RTF")
        documentFormat = DMPConnect.DocumentFormat.RtfText;
    else if (documentFormatString === "TIFF")
        documentFormat = DMPConnect.DocumentFormat.TiffImage;
    else if (documentFormatString === "TXT")
        documentFormat = DMPConnect.DocumentFormat.PlainText;
    else if (documentFormatString === "VSM")
        documentFormat = DMPConnect.DocumentFormat.VsmDocument;
    else if (documentFormatString === "CrBio")
        documentFormat = DMPConnect.DocumentFormat.CrBiologie;
    else if (documentFormatString === "DLU")
        documentFormat = DMPConnect.DocumentFormat.DluDocument;
    else if (documentFormatString === "DLU-FLUDT")
        documentFormat = DMPConnect.DocumentFormat.DluFludtDocument;
    else if (documentFormatString === "DLU-FLUDR")
        documentFormat = DMPConnect.DocumentFormat.DluFludrDocument;
    else
    {
        console.error("Document format not handled : " + documentFormatString)
        return 0;
    }
    return documentFormat;
}

function clickVisibility(aCheckbox)
{
    if (aCheckbox.name == "checkNormal")
    {
        document.getElementById("checkPsHidden").checked = false;
        document.getElementById("checkPatientHidden").checked = false;
        document.getElementById("checkGuardianHidden").checked = false;
    }
    if (aCheckbox.name == "checkPsHidden")
    {
        document.getElementById("checkNormal").checked = false;
        document.getElementById("checkPatientHidden").checked = false;
        document.getElementById("checkGuardianHidden").checked = false;
    }
    if (aCheckbox.name == "checkPatientHidden")
    {
        document.getElementById("checkNormal").checked = false;
        document.getElementById("checkPsHidden").checked = false;
    }
    if (aCheckbox.name == "checkGuardianHidden")
    {
        document.getElementById("checkNormal").checked = false;
        document.getElementById("checkPsHidden").checked = false;
    }
}

function getDocumentVisibility()
{
    if (document.getElementById("checkNormal").checked)
    {
        return DMPConnect.DocumentVisibility.Normal;
    }

    if (document.getElementById("checkPsHidden").checked)
    {
        return DMPConnect.DocumentVisibility.HealthcareProfesionnalHidden;
    }

    if (document.getElementById("checkPatientHidden").checked)
    {
        if (document.getElementById("checkGuardianHidden").checked)
        {
            return 10; // PatientHidden | GuardianHidden
        }
        return DMPConnect.DocumentVisibility.PatientHidden;
    }

    if (document.getElementById("checkGuardianHidden").checked)
    {
        return DMPConnect.DocumentVisibility.GuardianHidden;
    }

    return 1; // Normal by default
}
// Document table management
function clearDocumentsTable()
{
    var documentListTable = document.getElementById("documentList");
    while (documentListTable.firstChild) {
        documentListTable.removeChild(documentListTable.firstChild);
    }
}

function removeDocument(aDocumentIndex)
{
    g_documents.splice(aDocumentIndex, 1);
    updateDocumentsTable();
}


function updateDocumentsTable()
{
    var documentListTable = document.getElementById("documentList");
    clearDocumentsTable();

    // Header: title-category-delete
    var rowHeader = documentListTable.insertRow(-1);
    var rowTitle = rowHeader.insertCell();
    var rowCategory = rowHeader.insertCell();
    var rowDelete = rowHeader.insertCell();

    var rowTitleText = document.createTextNode("Title");
    var rowCategoryText = document.createTextNode("Category");
    var rowDeleteText = document.createTextNode("Delete");

    rowTitle.appendChild(rowTitleText);
    rowCategory.appendChild(rowCategoryText);
    rowDelete.appendChild(rowDeleteText);

    for (var i = 0; i < g_documents.length; ++i)
    {
        var title = g_documents[i].s_title;
        var cat = g_documents[i].s_category;

        var newRow = documentListTable.insertRow(-1);
        rowTitle = newRow.insertCell();
        rowCat = newRow.insertCell();
        rowDelete = newRow.insertCell();

        rowTitleText = document.createTextNode(title);
        rowCategoryText = document.createTextNode(cat);

        var btn = document.createElement("input");
        btn.setAttribute("type", "button");
        btn.setAttribute("value", "X");
        btn.setAttribute("name", "" + i);
        btn.onclick = (function (entry) {
            console.log("Erase document element " + (this.name));
            removeDocument(this.name);
        });

        rowTitle.appendChild(rowTitleText);
        rowCat.appendChild(rowCategoryText);
        rowDelete.appendChild(btn);
    }
}

function addDocumentToGlobalList(aDocumentTitle,
        aDocumentDescription,
        aDocumentCategory,
        aDocumentVisibility /* int */,
        aDocumentFormat /* int */,
        aDocumentContentInbase64,
        aDocumentAdditionalAuthorList,
        aReplacedDocumentOid,
        aDocumentCreationDate,
        aDocumentServiceStartDate,
        aDocumentServiceStopDate,
        aDocumentEventCodes,
        aDocumentInformantList,
        aTreatingPhysician)
{
    var doc = {}

    doc.s_title = aDocumentTitle
    doc.s_description = aDocumentDescription
    doc.s_category = aDocumentCategory;
    doc.i_visibility = aDocumentVisibility;
    doc.i_format = aDocumentFormat;
    doc.s_contentInBase64 = aDocumentContentInbase64;
    doc.s_replacedDocumentUniqueId = aReplacedDocumentOid;
    doc.s_creationDate = aDocumentCreationDate
    doc.s_serviceStartDate = aDocumentServiceStartDate
    doc.s_serviceStopDate = aDocumentServiceStopDate
    doc.EventCodes = aDocumentEventCodes;
    if (aDocumentInformantList.length > 0)
    {
        doc.Informants = aDocumentInformantList;
    }
    if (aTreatingPhysician !== undefined)
    {
        doc.TreatingPhysician = aTreatingPhysician;
    }
    if (aDocumentAdditionalAuthorList)
    {
        doc.AdditionalAuthors = aDocumentAdditionalAuthorList;
    }

    g_documents.push(doc);
    updateDocumentsTable();
}

function clearDocumentList()
{
    g_documents = [];
    updateDocumentsTable();
}

function prepareForNextDocumentAdd()
{
    document.getElementById("documentTitle").value = "";
    document.getElementById("documentDescription").value = "";
    document.getElementById("documentCategory").value = "11488-4";
    document.getElementById("checkNormal").checked = false;
    document.getElementById("checkPsHidden").checked = false;
    document.getElementById("checkPatientHidden").checked = false;
    document.getElementById("checkGuardianHidden").checked = false;
    document.getElementById("documentFormat").value = "JPEG";
    document.getElementById("replacedDocumentOid").value = "";
    document.getElementById("documentCreationDate").value = "";
    document.getElementById("documentServiceStartDate").value = "";
    document.getElementById("documentServiceStopDate").value = "";
    g_documentContent = undefined;
    document.getElementById("treatingPhysician").selectedIndex = -1;
    g_eventCodes = [];
    g_informants = [];

    updateInformantTable();
    updateEventCodeTable();

    var preFileContent = document.getElementById("file-content");
    while (preFileContent.firstChild)
    {
        preFileContent.removeChild(preFileContent.firstChild);
    }

    g_additionalAuthorList = [];
    updateAdditionalAuthorList();

}

function addDocumentToList()
{
    var documentTitle = document.getElementById("documentTitle").value;                // Mandatory
    var documentDescription = document.getElementById("documentDescription").value;          // Mandatory
    var documentCategory = document.getElementById("documentCategory").value;             // Mandatory
    var documentVisibility = getDocumentVisibility();                                         // Mandatory
    var documentFormat = getCurrentDocumentFormat();                                      // Mandatory
    var documentFile = g_documentContent;                                               // Mandatory
    var replacedDocumentOid = document.getElementById("replacedDocumentOid").value;
    var documentCreationDate = document.getElementById("documentCreationDate").value;
    var documentServiceStartDate = document.getElementById("documentServiceStartDate").value;
    var documentServiceStopDate = document.getElementById("documentServiceStopDate").value;

    // Checks for mandatory elements
    if (documentTitle.length == 0)
    {
        console.error("Document title is empty.");
        return;
    }
    if (documentDescription.length == 0)
    {
        console.error("Document description is empty.");
        return;
    }
    if (documentCategory.length == 0)
    {
        console.error("Document category is empty.");
        return;
    }
    if (documentFormat == 0)
    {
        console.error("Document format is invalid.");
        return;
    }
    if (g_documentContent == undefined)
    {
        console.error("Document content is not defined.");
        return;
    }

    // Treating physician
    var treatingPhysicianList = document.getElementById("treatingPhysician");
    var treatingPhysicianListSelectedIndex = treatingPhysicianList.selectedIndex;
    if (treatingPhysicianListSelectedIndex != -1)
    {
        var treatingPhysician = undefined;
        var treatingPhysicianIndex = parseInt(treatingPhysicianList.options[ treatingPhysicianListSelectedIndex ].value);
        if (treatingPhysicianIndex >= 0)
        {
            treatingPhysician = g_hpList[ treatingPhysicianIndex ];
        }
    }

    addDocumentToGlobalList(documentTitle,
            documentDescription,
            documentCategory,
            documentVisibility,
            documentFormat,
            documentFile,
            g_additionalAuthorList,
            replacedDocumentOid,
            documentCreationDate,
            documentServiceStartDate,
            documentServiceStopDate,
            g_eventCodes,
            g_informants,
            treatingPhysician);

    // Prepare for next add.
    prepareForNextDocumentAdd();
}

function getSubmissionSetTitle( )
{
    return document.getElementById("submissionSetTitle").value;
}

function getSubmissionSetDescription( )
{
    return document.getElementById("submissionSetDescription").value;
}

function sendDocument()
{
    if (g_documents.length == 0)
    {
        console.error("Document list is empty.");
        return;
    }

    var documentTitle = g_documents[0].s_title;
    var documentDescription = g_documents[0].s_description;
    var documentCategory = g_documents[0].s_category;
    var documentVisibility = g_documents[0].i_visibility;
    var documentFormat = g_documents[0].i_format;
    var documentContent = g_documents[0].s_contentInBase64;
    // var documentReplacedOid         = g_documents[0].s_replacedDocumentUniqueId;
    // var documentCreationDate        = g_documents[0].s_creationDate ;
    // var documentServiceStartDate    = g_documents[0].s_serviceStartDate;
    // var documentServiceStopDate     = g_documents[0].s_serviceStopDate;
    // var documentPerformer           = g_documents[0].Performer;
    var documentEventCodes = g_documents[0].EventCodes;
    var documentAuthors = g_documents[0].AdditionalAuthors;
    var documentInformants = g_documents[0].Informants;
    var documentTreatingPhysician = g_documents[0].TreatingPhysician;

    hl_sendDocument(documentContent,
            documentTitle,
            documentDescription,
            documentFormat,
            documentCategory,
            documentEventCodes,
            documentInformants,
            documentTreatingPhysician,
            documentVisibility,
            documentAuthors);
}

function sendDocuments()
{
    if (g_documents.length == 0)
    {
        console.error("Document list is empty.");
        return;
    }

    hl_sendDocuments(g_documents)
}


function listPcscSmartcardReaders()
{
    optionalParameters = undefined;

    dmpConnectInstance.hl_openSession(3600, optionalParameters, function (a) {
        dmpConnectInstance.hl_getPcscReaders(function (a) {
            dmpConnectInstance.hl_closeSession(function (a) {
                if (a.error) {
                    appendLog("-- listPcscSmartcardReaders failed: ", a);
                } else {
                    appendLog("-- listPcscSmartcardReaders succeeded.", a);
                }
            });
        })
    });
}

function readVitaleCard()
{
    optionalParameters = undefined;

    dmpConnectInstance.hl_openSession(3600, optionalParameters, function (a) {
        dmpConnectInstance.hl_getVitaleCard(getVitaleReaderIndex(), getVitaleReaderName(), function (a) {
            dmpConnectInstance.hl_readVitaleCard(function (a1) {
                dmpConnectInstance.hl_closeSession(function (a) {
                    if (a.error) {
                        appendLog("-- readVitaleCard failed: ", a1);
                    } else {
                        appendLog("-- readVitaleCard succeeded.", a1);
                        g_vitalePatientDatas = a1.Patients;
                        
                        appendDemoPatientsFromVitaleData(a1, a1.i_cardStatus);
                        initVitalePatientSelector();
                    }
                });
            });
        });
    });
}

function readCPxCard()
{
    optionalParameters = undefined;

    dmpConnectInstance.hl_openSession(3600, optionalParameters, function (a) {
        dmpConnectInstance.hl_getCpxCard(getCpxReaderIndex(), getCpxReaderName(), function (a) {
            dmpConnectInstance.hl_readCpxCard(getCpsPinCode(), function (a) {
                if (a.error) {
                    appendLog("-- readVitaleCard failed: ", a);
                } else {
                    appendLog("-- readVitaleCard succeeded.", a);
                    fillPracticeLocationList(a);
                    g_cpsData = a;
                }
            });
        });
    });
}

// ---------------------------------------------------------------------------------------------------------------------------------

function registerDcParameter( )
{
    var dcparam = document.getElementById("registrationDcParameter").value

    var command =
            {
                "s_dcparameters64": dcparam
            };

    dmpConnectInstance.sendHttpCommand(command, "registerDcParameter", function (e)
    {
        if (e.error)
        {
            appendLog("/registerDcParameter failed: ", e);
        } else
        {
            appendLog("/registerDcParameter succeeded: ", e);
        }
    });
}

function unregisterDcParameter( )
{
    var dcparam = document.getElementById("registrationDcParameter").value

    var command =
            {
                "s_dcparameters64": dcparam
            }

    dmpConnectInstance.sendHttpCommand(command, "unregisterDcParameter", function (e)
    {
        if (e.error)
        {
            appendLog("/unregisterDcParameter failed: ", e);
        } else
        {
            appendLog("/unregisterDcParameter succeeded: ", e);
        }
    });
}

function isDcParameterRegistered()
{
    var dcparam = document.getElementById("registrationDcParameter").value

    var command =
            {
                "s_dcparameters64": dcparam
            }

    dmpConnectInstance.sendHttpCommand(command, "isDcParameterRegistered", function (e)
    {
        if (e.error)
        {
            appendLog("/isDcParameterRegistered failed: ", e);
        } else
        {
            appendLog("/isDcParameterRegistered succeeded: ", e);
        }
    });
}

function getAllowedOrigins( )
{
    var command = {};

    dmpConnectInstance.sendHttpCommand(command, "getAllowedOrigins", function (e)
    {
        if (e.error)
        {
            appendLog("/getAllowedOrigins failed: ", e);
        } else
        {
            appendLog("/getAllowedOrigins succeeded: ", e);
        }
    });
}


// ---------------------------------------------------------------------------------------------------------------------------------
function hl_openSession()
{
    optionalParameters = undefined;
//modife adam
    var proxyServer = "";
    var proxyPort = "";
    var proxyLogin = "";
    var proxyPassword = "";

    var applicationId = "";

    if (proxyServer.length > 0 && proxyPort.length > 0)
    {
        iPort = parseInt(proxyPort, 10);

        if (!isNaN(iPort))
        {
            optionalParameters = {};

            optionalParameters.s_proxyIpOrUrl = proxyServer;
            optionalParameters.i_proxyPort = iPort;
            optionalParameters.s_proxyLogin = proxyLogin;
            optionalParameters.s_proxyPassword = proxyPassword;
        }
    }

    if (applicationId.length > 0)
    {
        if (optionalParameters == undefined)
        {
            optionalParameters = {};
        }

        optionalParameters.s_applicationId = applicationId;
    }

    dmpConnectInstance.hl_openSession(3600, optionalParameters, function (a) {
        if (a.error) {
            appendLog("-- hl_openSession failed: ", a);
        } else {
            appendLog("-- hl_openSession succeeded.", a);
        }
    });
}

function hl_openSessionWithCustomDcParam()
{
    optionalParameters = undefined;

    var proxyServer = getTextValue("sessionProxyServer");
    var proxyPort = getTextValue("sessionProxyPort");
    var proxyLogin = getTextValue("sessionProxyUsername");
    var proxyPassword = getTextValue("sessionProxyPassword");

    var applicationId = getTextValue("applicationId");

    if (proxyServer.length > 0 && proxyPort.length > 0)
    {
        iPort = parseInt(proxyPort, 10);

        if (!isNaN(iPort))
        {
            optionalParameters = {};

            optionalParameters.s_proxyIpOrUrl = proxyServer;
            optionalParameters.i_proxyPort = iPort;
            optionalParameters.s_proxyLogin = proxyLogin;
            optionalParameters.s_proxyPassword = proxyPassword;
        }
    }

    if (applicationId.length > 0)
    {
        if (optionalParameters == undefined)
        {
            optionalParameters = {};
        }

        optionalParameters.s_applicationId = applicationId;
    }

    var dcparam = document.getElementById("customDcParameter").value;
    dmpConnectInstance.hl_openSessionWithCustomDcParam(dcparam, 3600, optionalParameters, function (a)
    {
        if (a.error)
        {
            appendLog("-- hl_openSessionWithCustomDcParam failed: ", a);
        } else
        {
            appendLog("-- hl_openSessionWithCustomDcParam succeeded.", a);
        }
    });
}

function hl_startDummyMonitoring()
{
    var c = 0;

    dmpConnectInstance.hl_startDummyMonitoring(
            function (receivedObject, responseCallback)
            {
                if (receivedObject.error)
                {
                    appendLog("-- hl_startDummyMonitoring failed: ", receivedObject);
                } else if (c == 11)
                {
                    c = 0;
                    appendLog("-- hl_startDummyMonitoring succeeded.", a);
                } else
                {
                    console.log("Reception#" + c + ":\n" + printObject(receivedObject) + "\n");
                    if (c == 10)
                    {
                        c++;
                        responseCallback(false);
                    } else
                    {
                        c++;
                        responseCallback(true);
                    }
                }
            });
}

function hl_startCpxCardMonitoring()
{
    g_cpxMonitoringWS = dmpConnectInstance.hl_startCpxCardMonitoring(getCpxReaderCheckingInterval() /* Checking interval in seconds. */,
            function (receivedObject, responseCallback)
            {
                if (receivedObject.error)
                {
                    appendLog("-- hl_startCpxCardMonitoring failed: ", receivedObject);
                } else
                {
                    appendLog("The CPX card status changed:\n" + printObject(receivedObject) + "\n");

                    // To stop monitoring, one can either:
                    // - call responseCallback(false) ;
                    // - or close the socket ;
                    // - or close the session.

                    // Here we keep monitoring.
                    responseCallback(true);
                }
            });
}

function stopCPxCardMonitoring()
{
    g_cpxMonitoringWS.close();
}

function hl_startVitaleCardMonitoring()
{
    g_vitaleMonitoringWS = dmpConnectInstance.hl_startVitaleCardMonitoring(getVitaleReaderCheckingInterval() /* Checking interval in seconds. */,
            function (receivedObject, responseCallback)
            {
                if (receivedObject.error)
                {
                    appendLog("-- hl_startVitaleCardMonitoring failed: ", receivedObject);
                } else
                {
                    appendLog("The Vitale card status changed:\n" + printObject(receivedObject) + "\n");

                    // To stop monitoring, one can either:
                    // - call responseCallback(false) ;
                    // - or close the socket ;
                    // - or close the session.

                    // Here we keep monitoring.
                    responseCallback(true);

                    //appendLog("*** Closing the WS");
                    //ws.close();
                }
            }
    );
}


function stopVitaleCardMonitoring()
{
    g_vitaleMonitoringWS.close();
}



function hl_startPrintedDocumentsMonitoring()
{
    g_virtualPrinterMonitoringWS = dmpConnectInstance.hl_startPrintedDocumentsMonitoring(
            function (receivedObject, responseCallback)
            {
                if (receivedObject.error)
                {
                    appendLog("-- hl_startPrintedDocumentsMonitoring failed: ", receivedObject);
                } else
                {
                    appendLog("The virtual printer files changed:\n" + printObject(receivedObject) + "\n");

                    // To stop monitoring, one can either:
                    // - call responseCallback(false) ;
                    // - or close the socket ;
                    // - or close the session.

                    // Here we keep monitoring.
                    responseCallback(true);
                }
            }
    );
}

function hl_flushPrintedDocuments()
{
    dmpConnectInstance.hl_flushPrintedDocuments(undefined,
            function (a) {
                if (a.error) {
                    appendLog("-- hl_flushPrintedDocuments failed: ", a);
                } else {
                    appendLog("-- hl_flushPrintedDocuments succeeded.", a);
                }
            });
}

function hl_getPrintedDocumentsCount()
{
    dmpConnectInstance.hl_getPrintedDocumentsCount(function (a)
    {
        if (a.error)
        {
            appendLog("-- hl_getPrintedDocumentsCount failed: ", a);
        } else {
            appendLog("-- hl_getPrintedDocumentsCount succeeded.", a);
        }
    });
}

function hl_getPrintedDocuments()
{
    dmpConnectInstance.hl_getPrintedDocuments(function (a)
    {
        if (a.error)
        {
            appendLog("-- hl_getPrintedDocuments failed: ", a);
        } else
        {
            appendLog("-- hl_getPrintedDocuments succeeded.", a);
        }
    });
}

function hl_getNextPrintedDocument()
{
    dmpConnectInstance.hl_getNextPrintedDocument(function (a)
    {
        if (a.error)
        {
            appendLog("-- hl_getNextPrintedDocument failed: ", a);
        } else
        {
            appendLog("-- hl_getNextPrintedDocument succeeded.", a);
        }
    });
}

function stopPrintedDocumentsMonitoring()
{
    if (g_virtualPrinterMonitoringWS != undefined)
    {
        g_virtualPrinterMonitoringWS.close();
    }
}

function hl_getPcscReaders()
{
    dmpConnectInstance.hl_getPcscReaders
            (
                    function (a)
                    {
                        //    "Readers": [
                        //    {
                        //        "i_accessMode": 1,
                        //        "i_slotType": 3,
                        //        "s_accessMode": "Full PC/SC",
                        //        "s_name": "SCM Microsystems Inc. SCR331-DI Smart Card Reader 0",
                        //        "s_slotType": "CPS card"
                        //    },
                        //    {
                        //        "i_accessMode": 1,
                        //        "i_slotType": 2,
                        //        "s_accessMode": "Full PC/SC",
                        //        "s_name": "SCM Microsystems Inc. SCR35xx USB Smart Card Reader 0",
                        //        "s_slotType": "Vitale card"
                        //    },
                        //    {
                        //        "i_accessMode": 2,
                        //        "i_slotType": 3,
                        //        "s_accessMode": "GALSS/PCSC",
                        //        "s_name": "PSS Reader on CPS",
                        //        "s_slotType": "CPS card"
                        //    },
                        //    {
                        //        "i_accessMode": 2,
                        //        "i_slotType": 2,
                        //        "s_accessMode": "GALSS/PCSC",
                        //        "s_name": "Vitale",
                        //        "s_slotType": "Vitale card"
                        //    }
                        //],
                        //    "s_status": "OK"
                        //}

                        if (a.error)
                        {
                            appendLog("-- hl_getPcscReaders failed: ", a);
                        } else
                        {
                            appendLog("-- hl_getPcscReaders succeeded.", a);

                            if (a.Readers.length > 0)
                            {
                                // Set the "<select>" form data to show the available smartcard readers.
                                // comment adam
                                //var cpxSelectList    = document.getElementById( "listOfCpxReaderNames"    );
                                var vitaleSelectList = document.getElementById("listOfVitaleReaderNames");

                                for (var i = 0; i < a.Readers.length; i++)
                                {
                                    // comment adam
                                    //var cpxReaderNameEntry = document.createElement( "option" );
                                    //cpxReaderNameEntry.value = a.Readers[i].s_name;
                                    //cpxReaderNameEntry.text  = a.Readers[i].s_name + " (" + a.Readers[i].s_slotType + ")";
                                    //cpxSelectList.appendChild( cpxReaderNameEntry );

                                    //var vitaleNameEntry = document.createElement("option");
                                    //vitaleNameEntry.text = a.Readers[i].s_name + " (" + a.Readers[i].s_slotType + ")";
                                    //vitaleNameEntry.value = a.Readers[i].s_name;
                                    //vitaleSelectList.appendChild(vitaleNameEntry);
                                }

                                // Select a reader name based on the default reader indices.
                                // comment adam
                                //var cpxReaderIndex    = document.getElementById( "cpxReaderIndex"    ).value;
                                var vitaleReaderIndex = document.getElementById("vitaleReaderIndex").value;
                                // comment adam
                                //cpxSelectList.getElementsByTagName('option')[ cpxReaderIndex    ].selected = 'selected';
                                vitaleSelectList.getElementsByTagName('option')[ vitaleReaderIndex ].selected = 'selected';
                            }
                        }
                    }
            );
}

function hl_getVitaleCard()
{
    // 'SCM Microsystems Inc. SCR331-DI Smart Card Reader 0'
    dmpConnectInstance.hl_getVitaleCard(getVitaleReaderIndex(), getVitaleReaderName(), function (a) {
        if (a.error) {
            appendLog("-- hl_getVitaleCard failed: ", a);
        } else {
            appendLog("-- hl_getVitaleCard succeeded.", a);
        }
    });
}

function hl_signWithCpxCard()
{
    dmpConnectInstance.hl_signWithCpxCard(getCpsPinCode(), 'something random like stroumpfischtroumpfa', function (a) {
        if (a.error) {
            appendLog("-- hl_signWithCpxCard failed: ", a);
        } else {
            appendLog("-- hl_signWithCpxCard succeeded.", a);
        }
    });
}



function hl_readCpxCard()
{
    dmpConnectInstance.hl_readCpxCard(getCpsPinCode(), function (a) {
        if (a.error) {
            appendLog("-- hl_readCpxCard failed: ", a);
        } else {
            appendLog("-- hl_readCpxCard succeeded.", a);

            fillPracticeLocationList(a);
            g_cpsData = a;
        }
    });
}

// Given a valid result of hl_readCpxCard, fills the <select> values for the list of practice locations.
function fillPracticeLocationList(aCpxReadResult)
{
    var plListItem = document.getElementById("listOfPracticeLocationIndex");

    if (!plListItem)
        return;

    while (plListItem.firstChild)
    {
        plListItem.removeChild(plListItem.firstChild);
    }

    for (var i = 0; i < aCpxReadResult.PracticeLocations.length; ++i)
    {
        var opt = document.createElement("option");
        opt.text = aCpxReadResult.PracticeLocations[i].s_practiceLocationName;
        opt.value = i;

        plListItem.appendChild(opt);
    }
}

function hl_getCpxCard()
{
    dmpConnectInstance.hl_getCpxCard(getCpxReaderIndex(), getCpxReaderName(), function (a) {
        if (a.error) {
            appendLog("-- hl_getCpxCard failed: ", a);
        } else {
            appendLog("-- hl_getCpxCard succeeded.", a);
        }
    });
}

function hl_getCpxStatus()
{
    dmpConnectInstance.hl_getCpxStatus(function (a) {
        if (a.error)
        {
            appendLog("-- hl_getCpxStatus failed: ", a);
        } else
        {
            appendLog("-- hl_getCpxStatus succeeded.", a);
        }
    })
}

function hl_readVitaleCard()
{
    dmpConnectInstance.hl_readVitaleCard(function (a) {
        if (a.error) {
            appendLog("-- hl_readVitaleCard failed: ", a);
        } else {
            appendLog("-- hl_readVitaleCard succeeded.", a);
            g_vitalePatientDatas = a.Patients;
            initVitalePatientSelector();
            appendDemoPatientsFromVitaleData(a.Patients, a.i_cardStatus);
        }
    });
}

function hl_readVitaleCardCustom(returnData)
{
    dmpConnectInstance.hl_readVitaleCard(function (a) {
        if (a.error) {
            appendLog("-- hl_readVitaleCard failed: ", a);
        } else {
            appendLog("-- hl_readVitaleCard succeeded.", a);
            g_vitalePatientDatas = a.Patients;
            
            returnData(g_vitalePatientDatas);
            //initVitalePatientSelector();
            //appendDemoPatientsFromVitaleData(a.Patients, a.i_cardStatus);
        }
    });
}

function returnData(data)
{
    return data;  
}

function hl_getObjectParameters()
{
    var params =
            {
                i_objectHandle: getDocumentHandle()
                        // ,
                        // stringIds       : { Id0 : DMPConnect.ObjectParameterName.DocumentServiceStartDate,
                        //                     Id1 : DMPConnect.ObjectParameterName.DocumentCreationDate,
                        //                     Id2 : DMPConnect.ObjectParameterName.DocumentUniqueId
                        //                   },
                        // stringBase64Ids : { Id0 : DMPConnect.ObjectParameterName.DocumentTitle
                        //                   },
                        // integerIds      : { Id0 : DMPConnect.ObjectParameterName.DocumentPerformer,
                        //                     Id1 : DMPConnect.ObjectParameterName.DocumentAuthors
                        //                   },
                        // floatIds        : {}
            };

    dmpConnectInstance.hl_getObjectParameters(params, function (a) {
        if (a.error) {
            appendLog("-- hl_getObjectParameters failed: ", a);
        } else {
            appendLog("-- hl_getObjectParameters succeeded.", a);
        }
    });
}

function hl_getSessionState()
{
    dmpConnectInstance.hl_getSessionState(null, function (a) {
        if (a.error) {
            appendLog("-- hl_getSessionState failed: ", a);
        } else {
            appendLog("-- hl_getSessionState succeeded.", a);
        }
    });
}

function hl_closeSession()
{
    dmpConnectInstance.hl_closeSession(function (a) {
        if (a.error) {
            appendLog("-- hl_closeSession failed: ", a);
        } else {
            appendLog("-- hl_closeSession succeeded.", a);
        }
    });
}

function hl_checkUserLicenseRight()
{
    dmpConnectInstance.hl_checkUserLicenseRight(function (a)
    {
        if (a.error)
        {
            appendLog("-- hl_checkUserLicenseRight failed: ", a);
        } else
        {
            appendLog("-- hl_checkUserLicenseRight succeeded.", a);
        }
    });
}

// ---------------------------------------------------------------------------------------------------------------------------------
//      Session user data(s)
// ---------------------------------------------------------------------------------------------------------------------------------
function hl_setSessionData()
{
    var dataId = document.getElementById("sessionDataId").value;
    var dataContent = btoa(document.getElementById("sessionDataField").value);

    var command;

    if (dataId.length > 0)
    {
        command =
                {
                    "s_sessionDataId": dataId,
                    "s_sessionData64": dataContent
                };
    } else
    {
        command =
                {
                    "s_sessionData64": dataContent
                };
    }

    dmpConnectInstance.hl_setSessionData(command, function (a) {
        if (a.error) {
            appendLog("-- hl_setSessionData failed: ", a);
        } else {
            appendLog("-- hl_setSessionData succeeded.", a);
        }
    });
}

function hl_getSessionData()
{
    var dataId = document.getElementById("sessionDataId").value;

    var command =
            {
                "s_sessionDataId": dataId,
            };

    dmpConnectInstance.hl_getSessionData(command, function (a) {
        if (a.error) {
            appendLog("-- hl_getSessionData failed: ", a);
        } else {
            appendLog("-- hl_getSessionData succeeded.", a);

            var elt = document.getElementById("sessionDataField");
            elt.value = atob(a.s_sessionData64)
        }
    });
}

function hl_removeSessionData()
{
    var dataId = document.getElementById("sessionDataId").value;

    var command =
            {
                "s_sessionDataId": dataId,
            };

    dmpConnectInstance.hl_removeSessionData(command, function (a) {
        if (a.error)
        {
            appendLog("-- hl_removeSessionData failed: ", a);
        } else
        {
            appendLog("-- hl_removeSessionData succeeded.", a);
        }
    });
}

function hl_clearSessionData()
{
    dmpConnectInstance.hl_clearSessionData(function (a) {
        if (a.error)
        {
            appendLog("-- hl_clearSessionData failed: ", a);
        } else
        {
            appendLog("-- hl_clearSessionData succeeded.", a);
        }
    });
}

function hl_getSessionDataSize()
{
    dmpConnectInstance.hl_getSessionDataSize(function (a) {
        if (a.error)
        {
            appendLog("-- hl_getSessionDataSize failed: ", a);
        } else
        {
            appendLog("-- hl_getSessionDataSize succeeded.", a);
        }
    });
}

function hl_getMaximumSessionDataSize()
{
    dmpConnectInstance.hl_getMaximumSessionDataSize(function (a) {
        if (a.error)
        {
            appendLog("-- hl_getMaximumSessionDataSize failed: ", a);
        } else
        {
            appendLog("-- hl_getMaximumSessionDataSize succeeded.", a);
        }
    });
}

// ---------------------------------------------------------------------------------------------------------------------------------
//      Persistant user data
// ---------------------------------------------------------------------------------------------------------------------------------
function hl_setPersistantData()
{
    var dataContent = btoa(document.getElementById("persistantDataField").value);

    var command =
            {
                "s_persistantData64": dataContent
            };

    dmpConnectInstance.hl_setPersistantData(command, function (a) {
        if (a.error)
        {
            appendLog("-- hl_setPersistantData failed: ", a);
        } else
        {
            appendLog("-- hl_setPersistantData succeeded.", a);
        }
    });
}

function hl_getPersistantData()
{
    dmpConnectInstance.hl_getPersistantData(function (a) {
        if (a.error)
        {
            appendLog("-- hl_getPersistantData failed: ", a);
        } else
        {
            appendLog("-- hl_getPersistantData succeeded.", a);

            // Fill persistant text area with the content.
            var tarea = document.getElementById("persistantDataField");
            tarea.value = atob(a.s_persistantData64);
        }
    });
}

function hl_clearPersistantData()
{
    dmpConnectInstance.hl_clearPersistantData(function (a) {
        if (a.error)
        {
            appendLog("-- hl_clearPersistantData failed: ", a);
        } else
        {
            appendLog("-- hl_clearPersistantData succeeded.", a);
        }
    });
}

function hl_getPersistantDataSize()
{
    dmpConnectInstance.hl_getPersistantDataSize(function (a) {
        if (a.error)
        {
            appendLog("-- hl_getPersistantDataSize failed: ", a);
        } else
        {
            appendLog("-- hl_getPersistantDataSize succeeded.", a);
        }
    });
}

function hl_getMaximumPersistantDataSize()
{
    dmpConnectInstance.hl_getMaximumPersistantDataSize(function (a) {
        if (a.error)
        {
            appendLog("-- hl_getMaximumPersistantDataSize failed: ", a);
        } else
        {
            appendLog("-- hl_getMaximumPersistantDataSize succeeded.", a);
        }
    });
}



// ---------------------------------------------------------------------------------------------------------------------------------
function getLogsTail()
{
    dmpConnectInstance.getLogsTail({i_getServerLog: 1,
        i_getDmpConnectLog: 1,
        i_serverLogNbLastLines: 5
    }, function (a) {
        if (a.error) {
            appendLog("-- getLogsTail failed: ", a);
        } else {
            appendLog("-- getLogsTail succeeded.", a);
        }
    });
}

function hl_getSessionLogsTail()
{
    dmpConnectInstance.hl_getSessionLogsTail(
            {
                i_getServerLog: 1,
                i_getDmpConnectLog: 1,
                i_serverLogNbLastLines: 5
            }, function (a)
    {
        if (a.error)
        {
            appendLog("-- getLogsTail failed: ", a);
        } else
        {
            appendLog("-- getLogsTail succeeded.", a);
        }
    });
}

function getObjectParameterString()
{
    dmpConnectInstance.getObjectParameterString
            (
                    {
                        i_parameter: DMPConnect.ObjectParameterName.DocumentContent,
                        i_resultInBase64: 1, // Needed because the raw document content (binary) is returned.
                        // If the content is not 'plain text' then the binary content will break the JSON format.
                        i_object: getDocumentHandle()
                    },
                    function (a)
                    {
                        if (a.error)
                        {
                            appendLog("-- getParameterString failed: ", a);
                        } else
                        {
                            appendLog("-- getParameterString succeeded.", a);
                        }
                    }
            );
}
