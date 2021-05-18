/**
 * hl_getCpxCard must have been called.
 *
 * Optional parameters that can be set in 'optionalParameters' parameter:
 *   HTTP proxy:
 *     "s_proxyIpOrUrl"
 *     "i_proxyPort"
 *     "s_proxyLogin"
 *     "s_proxyPassword"
 *  Custom NTP server:
 *     "s_ntpIpOrUrl"
 *     "i_ntpPort"
 *  Connection Handle:
 *     "i_getConnectionHandle"      If set to 1, the webserver will return the handle of the connection used for DMP connection in addition to the unique instance ID
 *  Local patient Root OID
 *     "s_localPatientRootOid"
 *
 * Returns :
 * - the instance unique ID of the software. The number is unique per user account and do not change
 *  between restarts or software updates. It is used to identify the software instances that performed the DMP transactions.
 *
 * { [...]
 *    "s_instanceUniqueId":"2.25.186812548863990774547154938929646584355"
 * }
 * - if i_getConnectionHandle is set to 1, il also returns the connection handle used for Dmp transactions :
 * {
 *    [...]
 *    "i_connectionHandle" : 254678
 * }
 *
 *
 * @param {string}   pinCode                      E.g. "1234" for all test cards by default.
 * @param {string}   serverName                   Available values: "formation1", "formation2", "formation3", "formation5", "". See spec.
 *                                                Set it to an empty string to use the production DMP.
 * @param {number}   transactionsTimeoutInSeconds DMP transactions timeout.
 * @param {string}   practiceSetting              E.g. "AMBULATOIRE". See spec.
 * @param {number}   cpsPracticeLocationIndice    Practice location to use. Indice in the CPx card list of practices locations.
 * @param {function} resultCallback               The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_createDmpConnector = function( pinCode, serverName, transactionsTimeoutInSeconds, practiceSetting, cpsPracticeLocationIndice, optionalParameters, resultCallback )
{
    var serverUrl = "";

    // Empty string => production DMP.
    if( serverName === "" )
        serverUrl = "https://lps2.dmp.gouv.fr/si-dmp-server/v2/services";
    else
    {
        if( serverName.substr( 0 , 8 ) == "https://" )
        {
            serverUrl = serverName;
        }
        else 
        {
            serverUrl = "https://" + serverName + ".lps2.dmp.gouv.fr/si-dmp-server/v2/services"
        }
    }

    var command = {
        "s_commandName"               : "hl_createDmpConnector",
        "s_pinCode"                   : pinCode,
        "s_dmpUrl"                    : serverUrl,
        "i_transactionsTimeout"       : transactionsTimeoutInSeconds,
        "s_practiceSetting"           : practiceSetting,
        "i_cpsPracticeLocationIndice" : cpsPracticeLocationIndice,
        "s_sessionId"                 : this.getSessionId()
    };

    // Merge the HTTP proxy parameters and/or the NTP proxy parameters set in optionalParameters.
    if( optionalParameters )
    {
        for( var p in optionalParameters )
        {
            command[ p ] = optionalParameters[ p ];
        }
    }

    return this.sendCommand( command, 20, resultCallback, false, (function(self){
        return function(result) {
            if (result.s_status == 'OK') {
                self.setState('DmpConnector', true);
                self._refreshCallback();
            }
        }
  })(this));
};




/**
 * @brief Get Certified identity (TD00) for a given patient.
 * @param {string}  nirOD       Nir "Ouvrant-Droit"     (as found on Vitale Card)
 * @param {string}  birthDate   Patient birth date (in format YYYYMMDD)
 * @param {int}     birthRank   Patient birth rank (almost always 1, except for twins)
 * @param {string}  nirIndividu Patient NIR Individu (only if known)
 *
 *
 * Exemple of output:
  {
  "Ins":
    {
      "s_ins": "188102B17295264",
      "s_insType": "T"
    },
  "s_birthday": "19881012",
  "s_patientGivenName": "FIGATELLIX",
  "s_patientName": "",
  "s_status": "OK"
  }
 */
DMPConnect.prototype.hl_getCertifiedIdentity = function( nirOD, birthDate, birthRank, nirIndividu, resultCallback )
{
    var command =
    {
        "s_commandName" : "hl_getCertifiedIdentity",
        "s_sessionId"   : this.getSessionId(),
        "s_nirOD"       : nirOD ,
        "s_birthDate"   : birthDate,
        "i_birthRank"   : birthRank,
        "s_nirIndividu" : nirIndividu
    };

    return this.sendCommand( command , 20 , resultCallback,false, (function(self){
        return function(result) {
            if (result.s_status == 'OK') {
                self.setDmpState('certifiedIdentity', true);
                self._refreshCallback();
            }
        }
    })(this));
}

/**
  * Try to get INS-NIR from INS-C
  */
DMPConnect.prototype.hl_getInsNirFromInsC = function( insC, resultCallback )
{
    var command =
    {
        "s_commandName" : "hl_getInsNirFromInsC",
        "s_sessionId"   : this.getSessionId(),
        "s_insC"        : insC
    };

    return this.sendCommand( command, 30, resultCallback );
}


/**
 * @brief update the practice location (and setting) for the current dmp Connector
 * @param {int} practiceLocationIndice          Practice location to use. Indice in the CPx card list of practices locations.
 * @param {string}   practiceSetting            E.g. "AMBULATOIRE". See spec.
 */
DMPConnect.prototype.hl_setPracticeLocation = function( practiceSetting , practiceLocationIndice , resultCallback )
{
    var command =
    {
        "s_commandName"               : "hl_setPracticeLocation",
        "i_cpsPracticeLocationIndice" : practiceLocationIndice,
        "s_practiceSetting"           : practiceSetting,
        "s_sessionId"                 : this.getSessionId()
    };

    return this.sendCommand( command , 20 , resultCallback );
}

DMPConnect.DMPStatus =
{
    DMPExist    : 1,
    DMPIsClosed : 2,
    DMPNotFound : 3,
    DMPError    : 4
};

DMPConnect.Sex =
{
    UnknownSex : 1,
    Male       : 2,
    Female     : 3
};

DMPConnect.Gender =
{
    UnknownGender : 1,
    M             : 2,
    Mme           : 3
};

DMPConnect.UserAuthorizationStatus =
{
    AuthorizationError   : 1, /// Means that the DMP does not exists or was closed.
    AuthorizationExist   : 2,
    AuthorizationExpired : 3,
    AuthorizationDenied  : 4,
    NoAuthorization      : 5
};

/**
 * TD 0.2: Check DMP existence and the status of the authorizations of the PS on it.
 * See specs.
 *
 * hl_createDmpConnector must have been called.
 *
 * Example of output:
 * {
 * "ExistingTestAnswer": {
 *   "AdminData": {
 *     "Ins": {
 *       "s_ins": "255069999999934",
 *       "s_insType": "D"
 *     },
 *     "s_birthday": "19550615",
 *     "s_insC": "0448685716413283718907",              // Note return is optional: it only exists if patient has an INS-C
 *     "s_patientGivenName": "Nathalie",
 *     "s_patientName": "DESMAUX"
 *   },
 *   "i_dmpStatus": 1,
 *   "i_sex": 3,
 *   "i_userAuthorization": 2,
 *   "i_webAccessIsOpen": 1,
 *   "s_closingDate": "",
 *   "s_closingMessage": ""
 * },
 * "i_treatingPhysician": 0,
 * "s_status": "OK"
 *}
 *
 * @param {string}   ins              The INS to check.
 * @param {function} resultCallback   The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_getDirectAuthenticationDMPStatus = function(ins, resultCallback)
{
    var command = {
        "s_commandName" : "hl_getDirectAuthenticationDMPStatus",
        // INS can be given using the s_ins field :
        "s_ins"        : ins,
        // Or using the Ins structure :
        /*
        "Ins" :
        {
            "s_ins" : ins ,
            "s_insType" : "N"
        },
        */
        "s_sessionId"   : this.getSessionId()
    };

    return this.sendCommand(command, 6, resultCallback,false, (function(self){
        return function(result) {
            if (result.s_status == 'OK') {
                self.setDmpState('directAuthenticationStatus', true);
                self._refreshCallback();
            }
        }
    })(this));
};



/**
  * @brief Document status.
  */
DMPConnect.DocumentStatus =
{
    Approved   : 1,
    Deprecated : 2,
    Archived   : 4
};

/**
  * @brief Document confidentiality visibility types.
  */
DMPConnect.DocumentVisibility =
{
    Normal                       : 1,
    PatientHidden                : 2,
    HealthcareProfesionnalHidden : 4,
    GuardianHidden               : 8
};

/**
  * @brief Document formats.
  */
DMPConnect.DocumentFormat =
{
    // Unstructured documents.
    PlainText        : 1,
    RtfText          : 2,
    JpgImage         : 3,
    TiffImage        : 4,
    PdfApplication   : 5,

    // Structured documents.
    VsmDocument      : 6,
    CrBiologie       : 7,

    DluDocument      : 40,      // 'DLU - Admission en EHPAD
    DluFludtDocument : 41,      // 'DLU - Fiche de liaison d'urgence - transfert de l'EHPAD vers les urgences'
    DluFludrDocument : 42       // 'DLU - Fiche de liaison d'urgence - retour des urgences vers l'EHPAD'
} ;

/**
  * @brief Performer roles.
  */
DMPConnect.PerformerRole =
{
    Actor     : 1,
    MainActor : 2,
    Assistant : 3
};

/**
  * @brief Event code classification.
  */
DMPConnect.EventCodeType =
{
    CCAM          : 1, ///< Actes médicaux, y compris imagerie, anatomocytopathologie, …
    SNOMED_3_5_VF : 2, ///< Actes médicaux, y compris imagerie, anatomocytopathologie, …
    CIM_10        : 3, ///< Diagnostics
    LOINC         : 4, ///< Comptes rendus d'examens biologiques
    DRC           : 5  ///< Résultats de consultation de médecine générale.
};

/**
 * TD 3.1: Search for documents on the DMP.
 * See specs.
 *
 * hl_createDmpConnector must have been called.
 *
 * Any subsequent call will discard the document handles (i_handle).
 *
 * IN : All parameters are optional except "s_ins".
 *   "s_ins"
 *   "Categories"
 *   {
 *     "#0" : "46241-6",
 *     "#1" : "11488-4"
 *     ...
 *   }
 *   "Formats"
 *   {
 *     "#0", "urn:ihe:pat:apsr:larynx:2010"
 *   }
 *   "Practices"
 *   {
 *     "#0", "PALLIATIF"
 *   }
 *   "s_serviceStartDateTop"
 *   "s_serviceStartDateBottom"
 *   "s_creationDateTop"
 *   "s_creationDateBottom"
 *   "s_submissionDateTop"
 *   "s_submissionDateBottom"
 *   "i_status"     : See DMPConnect.DocumentStatus. Multiple choices are possible by adding values of this enum.
 *   "i_visibility" : See DMPConnect.DocumentVisibility. Multiple choices are possible by adding values of this enum.
 *   "i_disableMetadataSearch" 
 * OUT
 *
 * {
 *  "Documents": [
 *    {
 *      "Authors": [
 *        {
 *          "i_handle": 524290,
 *          "i_hpAuthenticationMode": 25,
 *          "s_hpGiven": "ALAIN",
 *          "s_hpInternalId": "899900023351",
 *          "s_hpName": "GENE RPPS",
 *          "s_hpProfession": "10",
 *          "s_hpProfessionOid": "1.2.250.1.71.1.2.7",
 *          "s_hpProfessionDescription": "Médecin",
 *          "s_hpSpeciality": "SM26",
 *          "s_hpSpecialityDescription": "Qualifié en médecine générale (SM)"
 *        }
 *      ],
 *      "EventCodes": [],
 *      "i_document_Format": 5,
 *      "i_document_Status": 1,
 *      "i_document_Visibility": 4,
 *      "i_handle": 131073,
 *      "s_classCode": "10",
 *      "s_creationDate": "20161110165758",
 *      "s_description": "Description très utile",
 *      "s_healthCareFacilityTypeCode": "SA07",
 *      "s_nextUUId": "",                           (*) Only available if metadata search is performed (default)
 *      "s_practiceSettingCode": "AMBULATOIRE",
 *      "s_previousUUId": "",                       (*) Only available if metadata search is performed (default)
 *      "s_serviceStartDate": "20161110165758",
 *      "s_serviceStopDate": "",
 *      "s_submissionDate": "",                     (*) Only available if metadata search is performed (default)
 *      "s_title": "Document de test DmpConnect JS",
 *      "s_typeCode": "46241-6",
 *      "s_uniqueId": "2.25.12275515752198996114817914428683693778",
 *      "s_uuid": "urn:uuid:00f8e428-a767-11e6-95bf-00163e58645d"
 *    },
 *    {
 *      "Authors": [*],
 *      "EventCodes": [*],
 *       *
 *    },
 *  ],
 *  "s_status": "OK"
 *}
 * @param {string}   ins              The INS of the DMP to look in.
 * @param {object}   command          Allow to extend the command for optionals parameters
 * @param {function} resultCallback   The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_findDocuments = function(ins, command, resultCallback)
{
    command = command || {};
    command.s_commandName = "hl_findDocuments";
    // INS number can be given the s_ins field :
    command.s_ins        = ins;
    // or using the Ins structure :
    // command.Ins =
    // {
    //     "s_ins" : ins ,
    //     "s_insType" : "N"
    // };
    command.s_sessionId   = this.getSessionId();

    return this.sendCommand(command, 600, resultCallback);
};

/**
  * @brief For TD 0.3 below.
  */
DMPConnect.UserAuthorizationAction =
{
    AddAuthorization    : 1,
    RemoveAuthorization : 2
};

/**
 * @brief TD 0.3 Update HP authorization on a DMP.
 * See specs.
 * hl_createDmpConnector must have been called.
 *
 * IN
 *   "s_ins"
 *   "i_action" : 1 = AddAuthorization, 2 = RemoveAuthorization. See UserAuthorizationAction enum.
 *   "i_setTreatingPhysician" : To remove the treating physician status, call the function with AddAuthorization and false.
 * OUT
 *
 * @param {string}   ins               The INS of the DMP we need to access or not.
 * @param {number}   action            The action to perform (i.e. add or remove the authorization). See UserAuthorizationAction.
 * @param {number}   treatingPhysician 1: enable "Medecin Traitant" access to the given DMP. Ignored if action == RemoveAuthorization
 * @param {object}   command           Allow to extend the command for optionals parameters
 * @param {function} resultCallback    The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_updateUserDmpAccessAuthorization = function(ins, action, treatingPhysician, command, resultCallback)
{
    command = command || {};
    command.s_commandName          = "hl_updateUserDmpAccessAuthorization";
    command.s_ins                  = ins;
    command.i_setTreatingPhysician = treatingPhysician;
    command.i_action               = action;
    command.s_sessionId            = this.getSessionId();

    return this.sendCommand(command, 10, resultCallback);
};

/**
 * @brief TD 0.4 Get accessible DMPs list for the current user (CPx mode) or structure (ES mode).
 * See specs.
 * hl_createDmpConnector must have been called.
 *
 * IN
 *   "s_type" : "LASTAUTORIZATION" or "LASTDOC"
 *   "s_date" : Format: YYYYMMDD
 * OUT
 * {
 *   "AccessibleDmps": [
 *     {
 *       "s_birthday": "19980121",
 *       "s_ins": "",
 *       "s_lastAccessDate": "20161121112348",
 *       "s_lastAddDate": "",
 *       "s_lastUpdateDate": "20161121111802",
 *       "s_patientBirthName": "",
 *       "s_patientGivenName": "THIBAULT",
 *       "s_patientName": "AATITROIS",
 *       "s_insC" : "0529528580088131833916",
 *       "i_treatingPhysician" : 1,
 *     },
 *     { ...
 *     },
 *     { ...
 *     }
 *   ],
 *   "s_status": "OK"
 * }
 *
 * @param {string}   type             "LASTAUTORIZATION" or "LASTDOC".
 * @param {string}   date             Format: YYYYMMDD.
 * @param {object}   command          Allow to extend the command for optionals parameters
 * @param {function} resultCallback   The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_getAccessibleDMPList = function(type, date, command, resultCallback)
{
    command = command || {};
    command.s_commandName = "hl_getAccessibleDMPList";
    command.s_type        = type;
    command.s_date        = date;
    command.s_sessionId   = this.getSessionId();

    return this.sendCommand(command, 30, resultCallback);
};

/**
 * @brief TD 3.2 Download a document from the DMP.
 * See specs.
 * hl_createDmpConnector must have been called.
 *
 * The document is stored in the system temporary directory.
 * Its URL is returned in 's_filePath'.
 *
 * IN
 *       "i_documentHandle"
 *   (*) "i_getCdaHeaders"     (Optional) Set it to 1 to retrieve CDA headers in HTML format
 *   (*) "i_getPerformer"      (Optional) Set it to 1 to retreive informations relative to the Performer (Hp data, and it's role).
 * OUT
 *   "s_filePath": Complete local file URL (client side).
 *                 Ex.: file:///C:/Users/cyrion/AppData/Local/Temp/5fd4d6d3-ed32-11e5-bb05-00163e58645d.txt
 *                 The name of the file is based on 'the uuid of the document'.
 *   "Performer"           :  Information relative to the Performer. (only present if i_getPerformer is set to 1).
 *                            The structure will contains the following data:
 *  
 *   "Performer": 
 *   {
 *     "Hp": 
 *     {
 *         "i_hpAuthenticationMode": 
 *         "i_hpInternalIdType": 
 *         "s_hpGiven": 
 *         "s_hpInstitution": 
 *         "s_hpInternalId": 
 *         "s_hpName": 
 *         "s_hpProfession": 
 *         "s_hpProfessionDescription": 
 *         "s_hpProfessionOid": 
 *         "s_hpSpeciality": 
 *         "s_hpSpecialityDescription": 
 *      },
 *      "i_role": 2                         Type : DMPConnect.PerformerRole 
 *    }
 *    "s_cdaHeadersInBase64":  CDA headers in HTML format. (only present if i_getCdaHeaders is set top 1).
 * 
 * @param {number}   hdoc             Handle of the document to download. Handles are returned by hl_findDocuments (in i_handle).
 * @param {object}   command          Allow to extend the command for optionals parameters
 * @param {function} resultCallback   The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_getDocumentContent = function(hdoc, command, resultCallback)
{
    command = command || {};
    command.s_commandName    = "hl_getDocumentContent";
    command.i_documentHandle = hdoc;
    command.i_getCdaHeaders  = 1;
    command.i_getPerformer   = 1;
    command.s_sessionId      = this.getSessionId();

    return this.sendCommand(command, 90, resultCallback);
};

/**
 * @brief TD 3.2 Download a document from the DMP.
 * See specs.
 * hl_createDmpConnector must have been called.
 *
 * IN
 *      s_ins                   Patient INS.
 *      s_documentUniqueId      Document Unique ID
 *  (*) s_documentUuid          Document UUID. If not provided, an additional transaction is performed.
 *  (*) i_getCdaHeaders         (Optional) Set it to 1 to retrieve CDA headers in HTML format 
 *  (*) i_getPerformer          (Optional) Set it to 1 to retreive informations relative to the Performer (Hp data, and it's role). * OUT
* OUT
 *      s_fileContentInBase64   Content of the document in Base64 format
 *      s_cdaHeadersInBase64    CDA headers in HTML format. (only present if i_getCdaHeaders is set top 1).
 *      i_documentFormat        Format of the document. (Values from DMPConnect.DocumentFormat)
 *      Performer               Information relative to the Performer. (only present if i_getPerformer is set to 1).
 *
 * 
 *  "Performer": 
 *   {
 *     "Hp": 
 *     {
 *         "i_hpAuthenticationMode": 
 *         "i_hpInternalIdType": 
 *         "s_hpGiven": 
 *         "s_hpInstitution": 
 *         "s_hpInternalId": 
 *         "s_hpName": 
 *         "s_hpProfession": 
 *         "s_hpProfessionDescription": 
 *         "s_hpProfessionOid": 
 *         "s_hpSpeciality": 
 *         "s_hpSpecialityDescription": 
 *      },
 *      "i_role": 2                         Type : DMPConnect.PerformerRole 
 *    } 
 * 
 * */
DMPConnect.prototype.hl_getDocumentContentByUniqueId = function (ins, uniqueId, uuid, command, resultCallback)
{
    command = command || {};
    command.s_commandName = "hl_getDocumentContentByUniqueId";
    command.s_sessionId   = this.getSessionId();

    command.s_ins              = ins;
    command.s_documentUniqueId = uniqueId;
    command.s_documentUuid     = uuid;
    command.i_getCdaHeaders    = 1;    
    command.i_getPerformer     = 1;

    return this.sendCommand(command, 90, resultCallback);
}


/**
 * @brief Toggle the Document visibility status between "Hidden to HP that are not treating physician"
 *  and "Visible to all HP" (TD 3.3). See spec.
 * IN
 *     "s_healthCareSettings"
 *     "i_document"
 *     "i_newVisibility"
 * OUT
 */
DMPConnect.prototype.hl_updateDocumentVisibility = function(hdoc, healthcareSettings, newVisibility, command, resultCallback)
{
    command = command || {};
    command.s_commandName        = "hl_updateDocumentVisibility";
    command.i_document           = hdoc;
    command.s_healthCareSettings = healthcareSettings;
    command.i_newVisibility      = newVisibility;
    command.s_sessionId          = this.getSessionId();

    return this.sendCommand(command, 5, resultCallback);
};

/**
 * @brief Toggle the Document visibility status between "Hidden to HP that are not treating physician"
 *  and "Visible to all HP" (TD 3.3). See spec.
 *
 * IN
 *         "s_ins"                 Patient INS.
 *         "s_documentUniqueId"    Document Unique Id.
 *   (*)   "s_documentUuid"        Document UUID. Optional only if current document status is 'Approved'. If not provided, an additional transaction is performed. Thus slowing down the command.
 *         "s_healthCareSettings"  Healthcare settings.
 *         "i_newVisibility"       New visibility.
 * OUT
 */
DMPConnect.prototype.hl_updateDocumentVisibilityByUniqueId = function( ins , documentUniqueId, documentUuid, healthcareSettings, newVisibility, command, resultCallback )
{
    command               = command || {};
    command.s_commandName = "hl_updateDocumentVisibilityByUniqueId";
    command.s_sessionId   = this.getSessionId();

    command.s_ins                = ins;
    command.s_documentUniqueId   = documentUniqueId;
    command.s_documentUuid       = documentUuid;
    command.s_healthCareSettings = healthcareSettings;
    command.i_newVisibility      = newVisibility;

    return this.sendCommand(command, 10, resultCallback);
}

/**
 * @brief Toggle status between 'Approved' and 'Archived' (TD 3.3). See spec.
 * IN
 *     "s_healthCareSettings"
 *     "i_document"
 * OUT
 */
DMPConnect.prototype.hl_updateDocumentStatus = function(hdoc, healthcareSettings, command, resultCallback)
{
    command = command || {};
    command.s_commandName        = "hl_updateDocumentStatus";
    command.i_document           = hdoc;
    command.s_healthCareSettings = healthcareSettings;
    command.s_sessionId          = this.getSessionId();

    return this.sendCommand(command, 5, resultCallback);
};

/**
 * @brief Toggle status between 'Approved' and 'Archived' (TD 3.3). See spec.
 * IN
 *         "s_ins"                 Patient INS.
 *         "s_documentUniqueId"    Document Unique Id.
 *   (*)   "s_documentUuid"        Document UUID. Optional only if current document status is 'Approved'. If not provided, an additional transaction is performed. Thus slowing down the command.
 *         "s_healthCareSettings"  Healthcare settings.
 * OUT
 */
DMPConnect.prototype.hl_updateDocumentStatusByUniqueId = function( ins, documentUniqueId, documentUuid, healthcareSettings, command, resultCallback )
{
    command               = command || {};
    command.s_commandName = "hl_updateDocumentStatusByUniqueId";
    command.s_sessionId   = this.getSessionId();

    command.s_ins                = ins;
    command.s_documentUniqueId   = documentUniqueId;
    command.s_documentUuid       = documentUuid;
    command.s_healthCareSettings = healthcareSettings;

    return this.sendCommand(command, 10, resultCallback);
}


/*
 * @brief Delete a document from the DMP. (TD 3.3). See spec.
 * IN
 *     "s_uniqueId"
 *     "s_ins"
 *     "s_healthCareSettings"
 * OUT
 */
DMPConnect.prototype.hl_deleteDocument = function(uid, ins, healthcareSettings, command, resultCallback)
{
    command = command || {};
    command.s_commandName        = "hl_deleteDocument";
    command.s_uniqueId           = uid;
    // INS number can be given s_ins field:
    command.s_ins               = ins;
    // or using the Ins structure:
    // command.Ins =
    // {
    //     "s_ins"     : ins,
    //     "s_insType" : "N"
    // };
    command.s_healthCareSettings = healthcareSettings;
    command.s_sessionId          = this.getSessionId();

    return this.sendCommand(command, 5, resultCallback);
};

/**
  * @brief Document visibility types.
  */
DMPConnect.DocumentVisibility =
{
    Normal                          : 1,
    PatientHidden                   : 2,
    HealthcareProfesionnalHidden    : 4,
    GuardianHidden                  : 8,
};


/**
 * @brief TD 2.1 Send a document to the DMP.
 *
 * hl_createDmpConnector must have been called.
 *
 * The document must be stored in a local file.
 *
 * IN
 *   "s_fileContentBase64",        // Document content in base64.
 *   "s_ins",
 *   "s_documentTitle",
 *   "s_documentDescription",
 *   "s_documentCategory",         // See specs section 9.1.5 "TABLE 5 CATEGORIES DE DOCUMENT (TYPECODE)".
 *   "i_documentVisibility",       // Normal=1. See enum DocumentVisibility.
 *   "i_documentFormat",           // PlainText = 1, PdfApplication = 5, etc. Cf. enum DocumentFormat.
 *   "s_healthcareSetting",        // See specs section 9.1.8 "TABLE 8 - CADRE DE SOINS (HEALTHCARE SETTINGS)".
 *   "s_replacedDocumentUniqueId", // Optional
 *   "s_submissionSetTitle",       // Optional.
 *   "s_submissionSetDescription"  // Optional.
 *   "s_localPatientId"            // Optional.
 *   "s_eventCodes"                // Optional.
 *   "i_transcodeTypeCode"         // Optional. If true (1), transform the value of "s_documentCategory" to make sure the most up to date value is sent to the DMP servers. If false (0), an obsolete value of "s_documentCategory" will raise an error. Default Off (0)
 *   "i_forceSchematronsValidation"// Optional. If true (1), enable schematrons validation for unstructured documents (default: off).
 *
 * OUT
 *   "s_uniqueId"                   // Sent document unique id (to replace or remove it, etc.)
 *
 * @param {string}   ins
 * @param {string}   fileContentBase64         Document content in base64.
 * @param {string}   documentTitle             Title of the document.
 * @param {string}   documentDescription       Description of the document.
 * @param {string}   documentCategory          See specs section 9.1.5 "TABLE 5 CATEGORIES DE DOCUMENT (TYPECODE)".
 * @param {number}   documentVisibility        Normal=1. See enum DocumentVisibility.
 * @param {number}   documentFormat            PlainText = 1, PdfApplication = 5, etc. Cf. enum DocumentFormat.
 * @param {string}   healthcareSetting         See specs section 9.1.8 "TABLE 8 - CADRE DE SOINS (HEALTHCARE SETTINGS)".
 * @param            replacedDocumentUniqueId  Optional
 * @param            submissionSetTitle        Optional.
 * @param            submissionSetDescription  Optional.
 * @param            EventCodes                Optional.            
 * @param {object}   command                   Allow to extend the command for optionals parameters
 * @param {function} resultCallback            The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_sendDocument = function( ins,
                                                 fileContentBase64,
                                                 documentTitle,
                                                 documentDescription,
                                                 documentCategory,
                                                 documentVisibility,
                                                 documentFormat,
                                                 healthcareSetting,
                                                 replacedDocumentUniqueId,
                                                 submissionSetTitle,
                                                 submissionSetDescription,
                                                 eventCodes,
                                                 command,
                                                 resultCallback)
{
    command = command || {};
    command.s_commandName               = "hl_sendDocument";

    // INS number can be given using the s_ins field:
    command.s_ins                      = ins;
    // Or using the Ins structure:
    // command.Ins =
    // {
    //     "s_ins"     : ins ,
    //     "s_insType" : "N"
    // };
    command.s_fileContentBase64         = fileContentBase64;
    command.s_documentTitle             = documentTitle;
    command.s_documentDescription       = documentDescription;
    command.s_documentCategory          = documentCategory;
    command.i_documentVisibility        = documentVisibility;
    command.i_documentFormat            = documentFormat;
    command.s_healthcareSetting         = healthcareSetting;
    if (eventCodes)                     command.s_eventCodes                = eventCodes;
    if (replacedDocumentUniqueId)       command.s_replacedDocumentUniqueId  = replacedDocumentUniqueId;
    if (submissionSetTitle)             command.s_submissionSetTitle        = submissionSetTitle;
    if (submissionSetDescription)       command.s_submissionSetDescription  = submissionSetDescription;
    command.s_sessionId                 = this.getSessionId();

    // May take a long time for big documents.
    return this.sendCommand(command, 200, resultCallback);
};

/**
  *
  * IN: 
  *
  * For each document, the following parameters are available:
  *
  * IN
  * {
  *    Documents :
  *    [
  *          {
  *              s_contentInBase64
  *              s_title
  *              s_description
  *              s_category
  *              i_visibility
  *              i_format
  *              s_replacedDocumentUniqueId      (*)
  *              EventCodes : []                 (*) // Array with event code objects.
  *              s_creationDate                  (*) // Format: YYYYMMDDhhmmss[+-]hhmm
  *              s_serviceStartDate              (*) // Formats: YYYYMMJJ, YYYYMMDDhhmm[+-]hhmm or YYYYMMDDhhmmss[+-]hhmm
  *              s_serviceStopDate               (*)
  *              Informants : [ { } ]            (*) // Array with informant objects.
  *              TreatingPhysician               (*) // Hp Structure.
  *              AdditionalAuthors : [ {} ]      (*) // Array of Hp Stucture 
  *          }
  *    ]
  *    s_ins
  *    s_healthcareSetting
  *    s_submissionSetTitle         (*)
  *    s_submissionSetDescription   (*)
  *    s_localPatientId             (*)
  *    i_transcodeTypeCode          (*) // If true (1), transform the value of "s_category" on each document to make sure the most up to date value is sent to the DMP servers. If false (0), an obsolete value of "s_category" will raise an error. Default Off (0)
  *    i_forceSchematronsValidation (*) // If true (1), enable schematrons validation for unstructured documents (default: off).
  * }
  * OUT 
  * {
  *     UniqueIds : Array of unique IDs, one per document, in the same order of the document array.   
  * }
  * (*) Optional arguments.
  */
DMPConnect.prototype.hl_sendDocuments = function( ins,
                                                  healthcareSetting,
                                                  submissionSetTitle,
                                                  submissionSetDescription,
                                                  localPatientId,
                                                  documents,
                                                  command,
                                                  resultCallback )
{
    command                 = command || {} ;
    command.s_commandName   = "hl_sendDocuments" ;
    command.s_sessionId     = this.getSessionId();

    command.s_ins = ins ;
    // Or using the Ins structure:
    // command.Ins =
    // {
    //     "s_ins"     : ins ,
    //     "s_insType" : "N"
    // };
    command.s_healthcareSetting = healthcareSetting ;
    if( submissionSetTitle )
        command.s_submissionSetTitle = submissionSetTitle ;
    if( submissionSetDescription )
        command.s_submissionSetDescription = submissionSetDescription ;
    if( localPatientId )
        command.s_localPatientId = localPatientId ;

    command.Documents = documents ;

    return this.sendCommand( command, 200 , resultCallback ) ;
}



/**
 * @brief TD 1.3a Get DMP administrative data.
 *
 * hl_createDmpConnector must have been called.
 *
 * IN
 *   "s_ins"
 * OUT
 *   DmpAdministrativeData_t structure. See C headers.
 *
 *   Example of answer:
 *   {
 *     "GuardianData" :
 *     {
 *       "CivilStatus" :
 *       {
 *         "i_gender" : 1
 *         "s_given" : paul
 *         "s_name" : lucien
 *       }
 *
 *       "ContactDetails" :
 *       {
 *         "s_address" :
 *         "s_addressComplement" :
 *         "s_city" :
 *         "s_landlinePhone" :
 *         "s_mail" :
 *         "s_mobilePhone" :
 *         "s_postalCode" :
 *       }
 *
 *       "i_role" : 2
 *     }
 *
 *     "PatientData" :
 *     {
 *       "ExtendedCivilStatus" :
 *       {
 *         "CivilStatus" :
 *         {
 *           "i_gender" : 3
 *           "s_given" : NATHALIE
 *           "s_name" : DESMAUX
 *         }
 *
 *         "i_sex" : 3
 *         "s_birthCountry" :
 *         "s_birthDay" : 19550614
 *         "s_birthName" : DESMAUX
 *       }
 *
 *       "ExtendedContactDetails" :
 *       {
 *         "ContactDetails" :
 *         {
 *           "s_address" : 5 BD ALEXANDRE OYON
 *           "s_addressComplement" : APARTEMENT 50
 *           "s_city" : MARSEILLE
 *           "s_landlinePhone" :
 *           "s_mail" :
 *           "s_mobilePhone" : 0687820756
 *           "s_postalCode" : 13000
 *         }
 *
 *         "s_country" : FRANCE
 *       }
 *
 *       "Ins" :
 *       {
 *         "s_ins" : 0448685716413283718907
 *         "s_insType" : C
 *       }
 *
 *     }
 *
 *     "PatientOppositions" :
 *     {
 *       "i_brisDeGlaceOpposition" : 0
 *       "i_centre15Opposition" : 0
 *     }
 *
 *     "i_guardian" : 2
 *   }
 *
 * @param {string}                  ins
 * @param {object}   command        Allow to extend the command for optionals parameters
 * @param {function} resultCallback The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_getDmpAdministrativeData = function(ins, command, resultCallback)
{
    command = command || {};
    command.s_commandName = "hl_getDmpAdministrativeData";

    // Use either s_ins field of Ins structure :
    /*
    command.Ins =
    {
         s_ins : ins ,
         s_insType : "N"
    };
    */
    command.s_ins        = ins;


    command.s_sessionId   = this.getSessionId();

    return this.sendCommand(command, 10, resultCallback);
};


/**
 * @brief TD 1.3b Update DMP administrative data.
 *
 * hl_createDmpConnector must have been called.
 *
 * IN
 *   DmpAdministrativeData_t structure. See C headers. (same format as the output of hl_getDmpAdministrativeData)
 *
 *   Example of input
 *   {
 *     "GuardianData" :
 *     {
 *       "CivilStatus" :
 *       {
 *         "i_gender" : 1
 *         "s_given" : paul
 *         "s_name" : lucien
 *       }
 *
 *       "ContactDetails" :
 *       {
 *         "s_address" :
 *         "s_addressComplement" :
 *         "s_city" :
 *         "s_landlinePhone" :
 *         "s_mail" :
 *         "s_mobilePhone" :
 *         "s_postalCode" :
 *       }
 *
 *       "i_role" : 2
 *     }
 *
 *     "PatientData" :
 *     {
 *       "ExtendedCivilStatus" :
 *       {
 *         "CivilStatus" :
 *         {
 *           "i_gender" : 3
 *           "s_given" : NATHALIE
 *           "s_name" : DESMAUX
 *         }
 *
 *         "i_sex" : 3
 *         "s_birthCountry" :
 *         "s_birthDay" : 19550614
 *         "s_birthName" : DESMAUX
 *       }
 *
 *       "ExtendedContactDetails" :
 *       {
 *         "ContactDetails" :
 *         {
 *           "s_address" : 5 BD ALEXANDRE OYON
 *           "s_addressComplement" : APARTEMENT 50
 *           "s_city" : MARSEILLE
 *           "s_landlinePhone" :
 *           "s_mail" :
 *           "s_mobilePhone" : 0687820756
 *           "s_postalCode" : 13000
 *         }
 *
 *         "s_country" : FRANCE
 *       }
 *
 *       "Ins" :
 *       {
 *         "s_ins" : 0448685716413283718907
 *         "s_insType" : C
 *       }
 *
 *     }
 *
 *     "PatientOppositions" :
 *     {
 *       "i_brisDeGlaceOpposition" : 0
 *       "i_centre15Opposition" : 0
 *     }
 *
 *     "i_guardian" : 2
 *   }
 * OUT
 *
 * @param {string}   dmpAdminData The administrative patient data structure
 * @param {function} resultCallback The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_updateDmpAdministrativeData = function( dmpAdminData , resultCallback )
{
    var command =
    {
        "DmpAdministrativeData" : dmpAdminData,
        "s_commandName"         : "hl_updateDmpAdministrativeData",
        "s_sessionId"           : this.getSessionId()
    };

    return this.sendCommand(command, 10, resultCallback);
}

DMPConnect.Sex =
{
    UnknownSex : 1,
    Male       : 2,
    Female     : 3
};

DMPConnect.Gender =
{
    UnknownGender : 1,
    M             : 2,
    Mme           : 3
};

DMPConnect.GuardianStatus =
{
    NoGuardianDefined : 1,
    GuardianDefined   : 2
};

DMPConnect.GuardianAction =
{
    IgnoreGuardianSetup : 1,
    AddGuardian         : 2,
    RemoveGuardian      : 3
};

DMPConnect.LegalRepresentantRole =
{
    Father           :  1,
    Mother           :  2,
    StepFather       :  3,
    StepMother       :  4,
    GrandFather      :  5,
    GrandMother      :  6,
    GrandGrandFather :  7,
    GrandGrandMother :  8,
    Aunt             :  9,
    Uncle            : 10,
    Brother          : 11,
    Sister           : 12,
    Guardian         : 13
};


/**
 * @brief TD 1.1 Create a Dmp.
 *
 * hl_createDmpConnector must have been called.
 *
 * See specs for the maximum size of each string field.
 *
 *  IN: M = Mandatory data.
 *
 *    M "DmpAdministrativeData"
 *           "PatientData"
 *               "s_ins"
 *               "ExtendedCivilStatus"
 *                   "CivilStatus"
 *                       "i_gender"
 *                       "s_name"
 *                       "s_given"
 *                   "s_birthName"
 *                   "s_birthDay"
 *                   "s_birthCountry"
 *                   "i_sex"
 *               "ExtendedContactDetails"
 *                   "ContactDetails"
 *                       "s_mobilePhone"
 *                       "s_landlinePhone"
 *                       "s_mail"
 *                       "s_address"
 *                       "s_addressComplement"
 *                       "s_postalCode"
 *                       "s_city"
 *                   "s_country"
 *           "PatientOppositionsStatus"
 *               "i_brisDeGlaceOpposition"
 *               "i_centre15Opposition"
 *           "i_guardian"
 *           "GuardianData"
 *               "i_role"
 *               "CivilStatus"
 *                   "i_gender"
 *                   "s_name"
 *                   "s_given"
 *               "ContactDetails"
 *                   "s_mobilePhone"
 *                   "s_landlinePhone"
 *                   "s_mail"
 *                   "s_address"
 *                   "s_addressComplement"
 *                   "s_postalCode"
 *                   "s_city"
 *    M "VitaleData"
 *          "s_name"
 *          "s_birthName"
 *          "s_given"
 *          "s_birthDay " Format: YYMMDD
 * OUT
 *
 * @param {object}   dmpAdministrativeData Structure. See specs.
 * @param {object}   vitaleData            Structure. See specs.
 * @param {object}   command               Allow to extend the command for optionals parameters
 * @param {function} resultCallback        The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_createDmp = function( dmpAdministrativeData,
                                              vitaleData,
                                              command          ,
                                              resultCallback   )
{
    command = command || {};
    command.s_commandName       = "hl_createDmp";

    command.DmpAdministrativeData = dmpAdministrativeData;
    command.VitaleData            = vitaleData;

    command.s_sessionId         = this.getSessionId();

    return this.sendCommand(command, 10, resultCallback);
};


/**
 * @brief Same as hl_createDmp but it adds web access creation or acknowledgement pdf creation.
 *          Web access is created if SMS or Email is defined
 *          Acknowledgement pdf is created if no SMS or Email defined
 * Options can be added related to the pdf creation
 *  "i_returnAsFile"        Indicate if output pdf is also returned as a file
 *
 * OUT
 *  "s_pdfFileContentInBase64"      Content of the pdf created
 *  "s_pdfFileUrl"                  Path of the created pdf if i_returnAsFile is 1
 *
 * @param {object}   dmpAdministrativeData Structure. See specs.
 * @param {object}   vitaleData            Structure. See specs.
 * @param {object}   command               Allow to extend the command for optionals parameters
 * @param {function} resultCallback        The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_createDmpAndOtp = function (dmpAdministrativeData,
                                                    vitaleData,
                                                    command,
                                                    resultCallback)
{
    command                       = command || {};
    command.s_commandName         = "hl_createDmpAndOtp";
    command.DmpAdministrativeData = dmpAdministrativeData;
    command.VitaleData            = vitaleData;
    command.s_sessionId           = this.getSessionId();

    return this.sendCommand(command, 20, resultCallback);
}


/**
 * @brief TD 1.2 Reactivate a Dmp that has been closed.
 *
 * hl_createDmpConnector must have been called.
 *
 * See specs for the maximum size of each string field.
 *
 *  IN: M = Mandatory data.
 *
 *    M "s_ins"
 *    M "i_gender"
 *    M "s_given"
 *    M "s_name"
 *    M "i_sex"
 *      "s_birthCountry"
 *    M "s_birthDay" Format: YYYYMMDD
 *    M "s_birthName"
 *      "s_address"
 *      "s_addressComplement"
 *      "s_city"
 *      "s_landlinePhone"
 *      "s_mail"
 *      "s_mobilePhone"
 *      "s_postalCode"
 *      "s_country"
 * OUT
 *
 * @param {string}   ins
 * @param {number}   gender
 * @param {string}   given
 * @param {string}   name
 * @param {number}   sex
 * @param {string}   birthCountry
 * @param {string}   birthDay
 * @param {string}   birthName
 * @param {string}   address
 * @param {string}   addressComplement
 * @param {string}   city
 * @param {string}   landlinePhone
 * @param {string}   mail
 * @param {string}   mobilePhone
 * @param {string}   postalCode
 * @param {string}   country
 * @param {object}   command        Allow to extend the command for optionals parameters
 * @param {function} resultCallback The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_reactivateDmp = function(ins              ,
                                                 gender           ,
                                                 given            ,
                                                 name             ,
                                                 sex              ,
                                                 birthCountry     ,
                                                 birthDay         ,
                                                 birthName        ,
                                                 address          ,
                                                 addressComplement,
                                                 city             ,
                                                 landlinePhone    ,
                                                 mail             ,
                                                 mobilePhone      ,
                                                 postalCode       ,
                                                 country          ,
                                                 command          ,
                                                 resultCallback   )
{
    command = command || {};
    command.s_commandName       = "hl_reactivateDmp";

    // INS can be specified using either the s_ins field :
    command.s_ins              = ins             ;
    // Or the Ins Structure
    // command.Ins =
    //     {
    //         s_ins : ins ,
    //         s_insType : "N"
    //     };

    command.i_gender            = gender           ;
    command.s_given             = given            ;
    command.s_name              = name             ;
    command.i_sex               = sex              ;
    command.s_birthCountry      = birthCountry     ;
    command.s_birthDay          = birthDay         ;
    command.s_birthName         = birthName        ;
    command.s_address           = address          ;
    command.s_addressComplement = addressComplement;
    command.s_city              = city             ;
    command.s_landlinePhone     = landlinePhone    ;
    command.s_mail              = mail             ;
    command.s_mobilePhone       = mobilePhone      ;
    command.s_postalCode        = postalCode       ;
    command.s_country           = country          ;

    command.s_sessionId         = this.getSessionId();

    return this.sendCommand(command, 10, resultCallback);
};

/**
 * @brief TD 0.5 Find DMP based on partial information.
 *
 * hl_createDmpConnector must have been called.
 *
 * See specs for the maximum size of each string field.
 *
 *  IN: M = Mandatory data.
 *  {
 *     s_name       [81] Part of the patient name. At least 2 characters.
 *     s_givenName  [61] Patient given name.
 *     s_birthday   [ 9] Patient birthday as YYYYMMDD.
 *     s_city       [39] City.
 *     s_postalCode [11] Postal code.
 *     i_sex             Sex enum.
 *   M i_approxName      Boolean
 *   M i_approxCity      Boolean
 *  }
 *  OUT
 *
 * @param {object}   command        The input parameters.
 * @param {function} resultCallback The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_findPatients = function( command          ,
                                                 resultCallback   )
{
    command = command || {};
    command.s_commandName = "hl_findPatients";
    command.s_sessionId   = this.getSessionId();

    return this.sendCommand( command, 10, resultCallback );
};

/**
 * @brief TD 1.4 Close a Dmp.
 *
 * hl_createDmpConnector must have been called.
 *
 * See specs for the maximum size of each string field.
 *
 *  IN:
 *    "s_ins"
 *    "s_reason"
 *    "s_name"
 *    "s_given"
 * OUT
 *
 * @param {string}   ins
 * @param {string}   reason
 * @param {string}   name
 * @param {string}   given
 *
 * @param {object}   command        Allow to extend the command for optionals parameters
 * @param {function} resultCallback The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_closeDmp = function( ins              ,
                                             reason           ,
                                             name             ,
                                             given            ,

                                             command          ,
                                             resultCallback   )
{
    command = command || {};
    command.s_commandName       = "hl_closeDmp";

    // INS can be given using s_ins field :
    command.s_ins              = ins  ;
    // Or using Ins structure
    // command.Ins =
    // {
    //     s_ins : ins ,
    //     s_insType : "N"
    // };

    command.s_reason            = reason;
    command.s_name              = name  ;
    command.s_given             = given ;

    command.s_sessionId         = this.getSessionId();

    return this.sendCommand(command, 10, resultCallback);
};

DMPConnect.AccessMode =
{
    NormalAccess : 1,
    BrisDeGlace  : 2,
    Centre15     : 3,
};

/**
 * @brief Set the DmpConnector access mode.
 *        See specifications, function setDmpConnectorDirectAuthenticationDmpAccessMode().
 * IN (M = Mandatory)
 * {
 *  M i_accessMode : Cf. DMPConnect.AccessMode enum.
 *  M s_reason     : For the "Bris de Glace" mode, holds the description of the reason for opening a DMP in this mode.
 * }
 * OUT
 *
 * @param {number}   accessMode Cf. DMPConnect.AccessMode enum.
 * @param {string}   reason     For the "Bris de Glace" mode, holds the description of the reason for opening a DMP in this mode.
 *
 * @param {object}   command        Allow to extend the command for optionals parameters (none currently)
 * @param {function} resultCallback The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_setDmpAccessMode = function( accessMode, reason, command, resultCallback )
{
    command = command || {};
    command.s_commandName = "hl_setDmpAccessMode";
    command.i_accessMode  = accessMode;
    command.s_reason      = reason;
    command.s_sessionId   = this.getSessionId();

    return this.sendCommand( command, 2, resultCallback );
};

/**
 * Values for OTP Channel type.
 */
DMPConnect.OTPChannelType =
{
    SMS  : 1,
    Mail : 2
};

/**
  * @brief Get OTP Channel value.
  *
  * If web access does not exists or given channel is not present, an error is returned.
  *
  * IN
  * {
  *     s_ins
  *     i_otpChannelType            1 for SMS, 2 for Mail
  * }
  * OUT
  * {
  *     s_otpChannelValue
  * }
  *
  * User must have an autorization access on the given DMP.
  *
  * @param {string} ins             INS of the patient.
  * @param {int} otpChannelType     Type of the channel to query. 1 for SMS ; 2 for Mail.
  * @param {function} resultCallback The callback that take the result from sendCommand
  */
DMPConnect.prototype.hl_getOtpChannelValue = function( ins, type, resultCallback )
{
    var command = {};

    command.s_commandName          = "hl_getOtpChannelValue";
    command.s_ins                  = ins;
    command.i_otpChannelType       = type;
    command.s_sessionId            = this.getSessionId();

    return this.sendCommand( command, 20, resultCallback );
};

/**
 * Set or reset the patient PDF form.
 *
 * If no channel is given, the function will try to reset the patient credentials.
 * It can fail if no OTP was defined in the past.
 *
 * The function must be called with either one of the channel defined, or none of them.
 *
 * Set the channel value to 'remove' to delete it, but please note that:
 * - One channel must remain defined.
 * - It is not possible to know if a channel is defined.
 *
 * IN * = optional
 * {
 *    s_ins
 * *  s_otpPhone :
 *   OR
 * *  s_otpEmail : set to 'remove' to delete it. It is impossible to remove both channels.
 * *  i_returnAsFile : if it is set to 1, the pdf file stored on the system (in a temporary directory) is returned.
 * }
 * OUT
 * {
 *    s_pdfFileContentInBase64
 *    s_pdfFileUrl : returned if i_returnAsFile is set to 1
 * }
 *
 * @param {string}   ins            INS of the patient.
 * @param {string}   otpPhone       Optional.
 * @param {string}   otpEmail       Optional.
 * @param {integer}  returnAsFile   Optional. Set to 1 to keep the generated pdf on the client temporary directory.
 * @param {function} resultCallback The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_getPatientWebAccessPdf = function( ins, otpPhone, otpEmail, returnAsFile, openPdf, resultCallback )
{
    otpPhone     = otpPhone     || "";
    otpEmail     = otpEmail     || "";
    returnAsFile = returnAsFile || 0;

    var command = {};
    command.s_commandName          = "hl_getPatientWebAccessPdf";
    command.s_ins                  = ins;
    command.i_openPdfAfterCreation = openPdf;

    if( otpPhone.length )
        command.s_otpPhone = otpPhone;

    if( otpEmail.length )
        command.s_otpEmail = otpEmail;

    command.i_returnAsFile = returnAsFile;

    command.s_sessionId    = this.getSessionId();

    return this.sendCommand( command, 20, resultCallback );
};

/**
 * TD 0.9: Get the Web PS DMP Urls for a patient.
 * IN * = optional
 * {
 *    s_ins
 * }
 * OUT
 * {
 *    Urls : [
 *       "url of TableauDeBord",
 *       "url of DossierPatient",
 *       "url of GestionDMPPatient",
 *       "url of Documents",
 *       "url of ParcoursDeSoins",
 *       "url of HistoriqueAcces",
 *       "url of Parametrages",
 *       "url of VolontesEtDroits",
 *    ]
 * }
 *
 * @param {string}   ins            INS of the patient.
 * @param {function} resultCallback The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_getWebPsDmpUrls = function( ins, resultCallback )
{
    var command = {};
    command.s_commandName  = "hl_getWebPsDmpUrls";
    // INS can be given using the s_ins field :
    command.s_ins         = ins;
    // or using the Ins structure:
    // command.Ins =
    // {
    //     "s_ins" : ins ,
    //     "s_insType" : "N"
    // } ;

    command.s_sessionId    = this.getSessionId();

    return this.sendCommand( command, 2, resultCallback );
};

/**
  * @brief Set custom webPS root URL.
  *
  * IN
  * {
  *     s_url
  * }
  * OUT
  * {
  *
  * }
  *
  * @param {string} url                 New root URL for the WebPS urls. Set it to an empty string to reset to the default value.
  * @param {function} resultCallback    The callback that take the result from sendCommand
  */
DMPConnect.prototype.hl_setWebPsRootUrl = function( url, resultCallback )
{
    var command = {};
    command.s_commandName = "hl_setWebPsRootUrl";
    command.s_url         = url;

    command.s_sessionId = this.getSessionId();

    return this.sendCommand( command, 2 , resultCallback );
}


/**
 * @brief Query the DMP parameters to get the majority age
 *
 * IN
 * {
 *
 * }
 * OUT
 * {
 *    i_majorityAge
 * }
 */
DMPConnect.prototype.hl_getMajorityAge = function( resultCallback )
{
    var command = {};
    command.s_commandName = "hl_getMajorityAge" ;
    command.s_sessionId   = this.getSessionId() ;

    return this.sendCommand( command , 20 , resultCallback ) ;
}


/**
 * @brief Create DMP creation acknowledgement pdf - it must be given to the patient if dmp were created without web access.
 *
 * @param {string}  anIns           INS of the patient.
 * @param {int}     aGender         Gender of the patient (type: DMPConnect.Gender).
 * @param {string}  aName           Name of the patient.
 * @param {string}  aGiven          Given of the patient.
 * @param {int}     returnAsFile    If equal to 1, URL to the pdf is returned.
 * @param {int}     openPdf         If equal to 1, URL to the pdf is returned and the pdf is opened with the system pdf default viewer.
 *
 * IN
 * {
 *  "s_ins"                 : "188102B17295264T",
 *  "i_returnAsFile"        : 1,
 *  "i_openPdfAfterCreation": 0,
 *  "CivilStatus":
 *  {
 *    "i_gender" : 2,
 *    "s_name"   : "CORSE",
 *    "s_given"  : "FIGATELLIX"
 *  }
 */
DMPConnect.prototype.hl_createAcknowledgementPdf = function (anIns, aGender, aName, aGiven, returnAsFile, openPdf, resultCallback)
{
    var command = {};
    command.s_commandName          = "hl_createAcknowledgementPdf";
    command.s_sessionId            = this.getSessionId();
    command.s_ins                  = anIns;
    command.i_returnAsFile         = returnAsFile;
    command.i_openPdfAfterCreation = openPdf;
    command.CivilStatus            =
        {
            "i_gender": aGender,
            "s_name": aName,
            "s_given": aGiven
        };

    return this.sendCommand(command, 30, resultCallback);
}


/**
 * @brief Set confidentiality level for DMP transactions.
 *
 * @param {int} aConfidentialityEnabler     Set to 1 to enable secret connection, set to 0 to disable it.
 */
DMPConnect.prototype.hl_setConfidentialityLevel = function( aConfidentialityEnabler, resultCallback )
{
    var command = {};
    command.s_commandName            = "hl_setConfidentialityLevel";
    command.s_sessionId              = this.getSessionId();
    command.i_enableSecretConnection = aConfidentialityEnabler;

    return this.sendCommand(command, 15, resultCallback );
}

DMPConnect.DmpAuthorizationListType =
{
    AllAuthorizations: 1,
    AuthorizedUsers: 2,
    DeniedUsers: 3
};

/**
 * @brief Get HP authorization list for a DMP.
 *
 * @param {string} anIns                    INS of the DMP to test.
 * @param {int} anAuthorizationListType     Authorization list type (using values in DMPConnect.DmpAuthorizationListType)
 *
 * IN
 * {
 *    "s_ins"
 *    "i_authorizationType" (*)         // Default value is 1.
 * }
 * OUT
 * {
 *      AuthorizationList :
 *      [
 *          {
 *              s_nationalId            // National Identifier.
 *              s_nationalIdType        // Hp national identifier type.
 *              s_name                  // Hp name.
 *              s_given                 // Hp given.
 *              s_specialityCode        // Hp speciality code. Eg. "G15_10/SM36".
 *              s_speciality            // Hp speciality display. Eg. "Oncologie option médicale (SM)".
 *              s_authorizationStart    // Hp authorization start date (format YYYYMMDDHHmmss).
 *              s_lastActionDate        // Hp last action date (format YYYYMMDDHHmmss).
 *              i_authorizationType     // Hp authorization type. Values in enum HpAuthorizationType (ie: 1 => AuthorizedHp, 2 => DeniedHp).
 *              i_generalPractitionner  // Set to 1 if physician is "médecin traitant".
 *          }
 *          ,
 *      ]
 * }
 */
DMPConnect.prototype.hl_getDmpAuthorizationsList = function( anIns, anAuthorizationListType, resultCallback )
{
    var command = {};
    command.s_commandName           = "hl_getDmpAuthorizationsList" ;
    command.s_sessionId             = this.getSessionId();
    command.s_ins                   = anIns;
    command.i_authorizationType     = anAuthorizationListType;

    return this.sendCommand(command, 30, resultCallback);
}

DMPConnect.prototype.hl_getDmpParameters = function( resultCallback )
{
    var command = {};
    command.s_commandName           = "hl_getDmpParameters" ;
    command.s_sessionId             = this.getSessionId();

    return this.sendCommand(command, 30, resultCallback );
}
