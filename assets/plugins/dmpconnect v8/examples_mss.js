// The result of an LDAP search.
var gSearchHpInfoResults = {};

// The set of attachments to use for hl_sendMssEmail and hl_generateMssAttachments. 
var gAttachmentList = [];


function clearHpInfosResultsTextFields() 
{
    document.getElementById("searchResultCommonName").value                 = "";
    document.getElementById("searchResultName").value                       = "";
    document.getElementById("searchResultGiven").value                      = "";
    document.getElementById("searchResultPostalAddress").value              = "";
    document.getElementById("searchResultPostalCode").value                 = "";
    document.getElementById("searchResultCity").value                       = "";
    document.getElementById("searchResultDepartement").value                = "";
    document.getElementById("searchResultCountry").value                    = "";
    document.getElementById("searchResultMail").value                       = "";
    document.getElementById("searchResultCiviliteExercice").value           = "";
    document.getElementById("searchResultTitle").value                      = "";
    document.getElementById("searchResultIdNat").value                      = "";
    document.getElementById("searchResultCodeProfession").value             = "";
    document.getElementById("searchResultCodeCategorieProfession").value    = "";
    document.getElementById("searchResultLibelleCategorieProfession").value = "";
    document.getElementById("searchResultSpecialiteOrdinale").value         = "";
    document.getElementById("searchResultOrganisation").value               = "";
    document.getElementById("searchResultRaisonSociale").value              = "";
    document.getElementById("searchResultCompany").value                    = "";
    document.getElementById("searchResultEnseigneCommerciale").value        = "";
}

function clearHpInfosResults() 
{
    var selectResultHpInfos = document.getElementById("selectResultHpInfos");
    while ( selectResultHpInfos.firstChild ) 
    {
        selectResultHpInfos.removeChild(selectResultHpInfos.firstChild);
    }
    clearHpInfosResultsTextFields()
}

function setHpInfosResults() 
{
    clearHpInfosResults();
    var selectResultHpInfos = document.getElementById("selectResultHpInfos");

    for ( var i = 0; i < gSearchHpInfoResults.length; ++i ) 
    {
        var opt     = document.createElement("option");
        opt.text    = gSearchHpInfoResults[i].s_commonName + " " + gSearchHpInfoResults[i].s_idNat;
        opt.value   = i;

        selectResultHpInfos.appendChild(opt);
    }
}
/**
  * @brief Helper function used to fill all <input> fields related to a PS that has been selected.
  * These fields corresponds to the result of the LDAP search.
  */
function onSelectHpResultInfo() 
{
    var selectResultHpInfos = document.getElementById("selectResultHpInfos");
    var selectionValue      = selectResultHpInfos.selectedIndex;

    if (selectionValue < gSearchHpInfoResults.length && selectionValue >= 0) 
    {
        var psToShow = gSearchHpInfoResults[selectionValue];
    
        document.getElementById("searchResultCommonName").value                 = psToShow.s_commonName;
        document.getElementById("searchResultName").value                       = psToShow.s_name;
        document.getElementById("searchResultGiven").value                      = psToShow.s_given;
        document.getElementById("searchResultPostalAddress").value              = psToShow.s_postalAddress;
        document.getElementById("searchResultPostalCode").value                 = psToShow.s_postalCode;
        document.getElementById("searchResultCity").value                       = psToShow.s_city;
        document.getElementById("searchResultDepartement").value                = psToShow.s_departement;
        document.getElementById("searchResultCountry").value                    = psToShow.s_country;
        document.getElementById("searchResultMail").value                       = psToShow.s_mail;
        document.getElementById("searchResultCiviliteExercice").value           = psToShow.s_civiliteExercice;
        document.getElementById("searchResultTitle").value                      = psToShow.s_title;
        document.getElementById("searchResultIdNat").value                      = psToShow.s_idNat;
        document.getElementById("searchResultCodeProfession").value             = psToShow.s_codeProfession;
        document.getElementById("searchResultCodeCategorieProfession").value    = psToShow.s_codeCategorieProfession;
        document.getElementById("searchResultLibelleCategorieProfession").value = psToShow.s_libelleCategorieProfession;
        document.getElementById("searchResultSpecialiteOrdinale").value         = psToShow.s_specialiteOrdinale;
        document.getElementById("searchResultOrganisation").value               = psToShow.s_organisation;
        document.getElementById("searchResultRaisonSociale").value              = psToShow.s_raisonSociale;
        document.getElementById("searchResultCompany").value                    = psToShow.s_company;
        document.getElementById("searchResultEnseigneCommerciale").value        = psToShow.s_enseigneCommerciale;
    }
    else 
    {
        clearHpInfosResultsTextFields()
    }
}
/**
  * @brief Query the LDAP Directory to get informations about a HP.
  *
  * @note The Search criteria are defined using the input fields of the HTML page.
  */
function hl_getMssHpInfos() 
{
    var sName           = getFormTextFieldValue(document.getElementById("ldapSearchName"));
    var sGiven          = getFormTextFieldValue(document.getElementById("ldapSearchGiven"));
    var sRPPS           = getFormTextFieldValue(document.getElementById("ldapSearchRPPS"));
    var sSpecialty      = getFormTextFieldValue(document.getElementById("ldapSearchSpecialty"));
    var sOrganization   = getFormTextFieldValue(document.getElementById("ldapSearchOrganization"));

    dmpConnectInstance.hl_getMssHpInfos(sName, sGiven, sRPPS, sSpecialty, sOrganization, function (a) {
        if (a.error) 
        {
            appendLog( "-- hl_getMssHpInfos failed: ", a );
            gSearchHpInfoResults = {}
            clearHpInfosResults();
        } 
        else 
        {
            appendLog( "-- hl_getMssHpInfos succeeded.", a );
            gSearchHpInfoResults = a.PSList;
            setHpInfosResults();
            onSelectHpResultInfo();
        }
    });
}

/**
  * @brief Sample request that gets the list of (MSS) emails of a user. 
  * 
  * @param mssRequestField The field (textarea or input) that will contains the request.
  * 
  * @note The session must have been created first, and CPx card must be set, because this function calls the function 
  *        hl_readCpxCard to get the RPPS number of the user. 
  */
function getListEmailRequest( mssRequestField ) 
{
    dmpConnectInstance.hl_readCpxCard(getCpsPinCode(), function (b) {
        if ( !b.error ) 
        {
            mssRequestField.value = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:ser=\"http://services.msg.mss.asip.fr/\">" +
                "<soapenv:Header/>" +
                "<soapenv:Body>" +
                "  <ser:listEmails>" +
                "    <userId>" + b.s_internalId /* RPPS number */ + "</userId>" +
                "  </ser:listEmails>" +
                "</soapenv:Body>" +
                "</soapenv:Envelope>";
        }
        else 
        {
            mssRequestField.value = "";
        }
    });
}

/**
  * @brief Helper function that creates the selected MSS request. 
  */
function onSelectSampleRequest() 
{
    var mssRequestSelector  = document.getElementById("mssRequestSelector");
    var mssWsUrlPath        = document.getElementById("mssWsUrlPath");
    var mssRequest          = document.getElementById("mssRequest");
    mssWsUrlPath.value      = "";
    mssRequest.value        = "";

    if ( mssRequestSelector.selectedIndex >= 0 ) 
    {
        var requestSample = mssRequestSelector[ mssRequestSelector.selectedIndex ].value;
        if ( requestSample == "listEmails" ) 
        {
            mssWsUrlPath.value = "/mss-msg-services/services/Annuaire/soap/v1/listEmails";
            getListEmailRequest( mssRequest );
        }
    }
}

/**
 * This example tests the function hl_sendMssRequest.
 * 
 * @Note The DmpConnector object must have been created first.
 */
function hl_sendMssRequest() 
{
    // The path of the request
    var requestPath = document.getElementById("mssWsUrlPath");

    // The request itself
    var request = document.getElementById("mssRequest");

    dmpConnectInstance.hl_sendMssRequest(requestPath.value, btoa(request.value), function (a) {
        if (a.error) 
        {
            appendLog(" -- hl_sendMssRequest failed.", a);
            var mssRequestResult = document.getElementById("mssRequestResult");
            mssRequestResult.value = "";
        }
        else 
        {
            var mssRequestResult = document.getElementById("mssRequestResult");
            mssRequestResult.value = atob(a.s_answerBodyInBase64);
            appendLog(" -- hl_sendMssRequest succeeded.", a);
        }
    });
}

/**
 * @brief Function used to show the current set of attachments.
 */
function updateAttachmentListUI() 
{
    var attachmentListTr = document.getElementById("attachmentListTr");

    /* Clear the existing list */
    while ( attachmentListTr.firstChild ) 
    {
        attachmentListTr.removeChild(attachmentListTr.firstChild);
    }
    if ( gAttachmentList.length == 0 )
        return;

    var table = document.createElement("table");
    table.setAttribute("style", "width:100%");
    var headerTableRow    = document.createElement("tr");
    var headerTableId     = document.createElement("td");
    var headerTableTitle  = document.createElement("td");
    var headerTableAction = document.createElement("td");

    headerTableId.innerHTML     = "#";
    headerTableTitle.innerHTML  = "Title";
    headerTableAction.innerHTML = "Action";
    headerTableRow.appendChild(headerTableId);
    headerTableRow.appendChild(headerTableTitle);
    headerTableRow.appendChild(headerTableAction);
    table.appendChild(headerTableRow);

    for ( var i = 0; i < gAttachmentList.length; ++i ) 
    {
        var id           = gAttachmentList[i].index;
        var title        = gAttachmentList[i].s_documentTitle;
        var attachRow    = document.createElement("tr");
        var attachId     = document.createElement("td");
        var attachTitle  = document.createElement("td");
        var attachAction = document.createElement("td");
        var anAction     = document.createElement("a");
        anAction.setAttribute("href", "javascript:removeAttachmentFromIndex(" + i + ")");
        anAction.innerHTML = "Remove";
        attachId.innerHTML = id;

        attachTitle.innerHTML = title;
        attachAction.appendChild(anAction);
        attachRow.appendChild(attachId);
        attachRow.appendChild(attachTitle);
        attachRow.appendChild(attachAction);
        table.appendChild(attachRow);
    }
    
    attachmentListTr.appendChild(table);
}

/**
 * Function used to remove an attachment from the set of attachments.
 * @param {int} anIndex Index of the attachment to remove. 
 */
function removeAttachmentFromIndex( anIndex ) 
{
    if ( ( gAttachmentList.length > anIndex ) && ( anIndex >= 0 ) ) 
    {
        gAttachmentList.splice(anIndex, 1);
    }
    updateAttachmentListUI();
}

/**
 * Function used to add an attachment from the UI.
 * Note: Only a simple validation is made (non empty fields for example) before appending the attachments.
 */
function appendAttachment() 
{
    var attachment      = {};
    var insSelect       = document.getElementById("attachmentIns");
    var insSelectIndex  = insSelect.selectedIndex;
    if ( insSelectIndex < 0 ) 
    {
        console.error("Invalid INS - append attachment list aborted.");
        return;
    }
    attachment.index                 = gAttachmentList.length;
    attachment.s_patientIns          = insSelect.options[insSelectIndex].value;
    attachment.s_documentTitle       = getFormTextFieldValue(document.getElementById("attachmentTitle"));
    attachment.s_documentDescription = getFormTextFieldValue(document.getElementById("attachmentDescription"));
    attachment.s_documentCategory    = getFormTextFieldValue(document.getElementById("attachmentCategory"));

    // Format 
    var formatSelect      = document.getElementById("attachmentFormat");
    var FormatSelectIndex = formatSelect.selectedIndex;
    if (FormatSelectIndex < 0) 
    {
        console.error("Attachement format is not set - append attachment list aborted");
        return;
    }
    attachment.i_documentFormat     = parseInt(formatSelect.options[FormatSelectIndex].value);

    attachment.s_healthcareSetting  = getFormTextFieldValue(document.getElementById("attachmentHealthcareSetting"));

    // Content: either a raw text or a file content. Tests the two and get the first one that is non empty.
    var textContent = getFormTextFieldValue(document.getElementById("attachmentContent"));
    var fileContent = document.getElementById("attachmentFileContent").innerHTML;
    
    // Example of fileContent: "data:text/plain;base64,VHJ1YyhtdWNoZSkgZXh0cuptZW1lbnQgcGFzc2lvbm5hbnQuDQoNCg=="
    attachment.s_fileContentInBase64 = (fileContent.length == 0) ? btoa(textContent) : fileContent.split(",")[1];
    
    if ( attachment.s_documentTitle.length == 0 ) 
    {
        console.error("Attachment title is empty - append attachment list aborted.");
        return;
    }
    if ( attachment.s_fileContentInBase64.length == 0 ) 
    {
        console.error("Attachment content is empty - append attachment list aborted.");
        return;
    }
    if ( attachment.s_documentCategory.length == 0 ) 
    {
        console.error("Attachment categoy is empty - append attachment list aborted.");
        return;
    }
    if ( attachment.s_healthcareSetting.length == 0 ) 
    {
        console.error("Attachement healthcare setting is empty - append attachment list aborted.");
        return;
    }
    gAttachmentList.push(attachment);
    updateAttachmentListUI();
}

function clearAttachmentList() 
{
    gAttachmentList = [];
    updateAttachmentListUI();
}

/**
 * Example function used to send an email.
 */
function hl_sendMssEmail() 
{
    var mssWsUrlPath = getFormTextFieldValue( document.getElementById("mssSendEmailWsUrlPath") );
    var sender       = getFormTextFieldValue( document.getElementById("mssSenderEmail")        );
    var recipient    = getFormTextFieldValue( document.getElementById("mssRecipient")          );
    var cc           = getFormTextFieldValue( document.getElementById("mssCC")                 );
    var bcc          = getFormTextFieldValue( document.getElementById("mssBCC")                );
    var title        = getFormTextFieldValue( document.getElementById("mssTitle")              );
    var content      = getFormTextFieldValue( document.getElementById("mssContent")            );
    var htmlBody     = document.getElementById("checkHtmlBody").checked ? 1 : 0;

    dmpConnectInstance.hl_sendMssEmail(mssWsUrlPath, sender, recipient, cc, bcc, title, btoa(content), htmlBody, gAttachmentList, function (a) {
        if (a.error) 
        {
            appendLog(" -- hl_sendMssEmail failed.", a);
        }
        else 
        {
            appendLog(" -- hl_sendMssEmail succeeded.", a);
        }
    });
}

/**
  * @brief Called when the user selects a file to use as attachment.
  */
function onSelectAttachmentFile() 
{
    var fileInput = document.getElementById("attachmentFilename");
    var filePath = fileInput.files[0];
    if (!filePath) 
    {
        return;
    }

    appendLog("Loading file ...");

    var reader = new FileReader();
    reader.onload = function (e) 
    {
        var content = e.target.result;
        // Display the file content loaded by the browser (in base 64).
        document.getElementById('attachmentFileContent').innerHTML = content;
    };

    // Get data in base 64.
    reader.readAsDataURL(filePath);
}

/**
  * Example function used to convert a list of attachement to a buffer usable in a sendMessage request.
  */
function hl_generateMssAttachments() 
{
    dmpConnectInstance.hl_generateMssAttachments(gAttachmentList, function (a) {
        if (a.error) 
        {
            appendLog(" -- hl_generateMssAttachments failed.", a);
        }
        else 
        {
            appendLog(" -- hl_generateMssAttachments succeeded.", a);
            var resultArea = document.getElementById("generateMssAttachementResult");
            resultArea.value = atob(a.s_attachmentsBufferInBase64);
        }
    });
}