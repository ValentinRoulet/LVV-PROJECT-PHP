// "formation1", "formation5", etc.
// An empty string => production DMP.
function getDmpServerName()
{
    return getFormTextFieldValue(document.getElementById('dmpServerName'));
}

function getSelectedIns()
{
    return getFormTextFieldValue(document.getElementById("TD00_nirIndividu_out"));
}

function getSelectedPatientName()
{
    return getFormTextFieldValue(document.getElementById("TD00_name_out"));
}

function getSelectedPatientGiven()
{
    return getFormTextFieldValue(document.getElementById("TD00_given_out"));
}

function getDocumentUniqueId()
{
    return getFormTextFieldValue(document.getElementById("docUniqueId"));
}

function getDocumentUuid()
{
    return getFormTextFieldValue(document.getElementById("docUuid"));
}

function computeSelectedPatientGender() {
    var ins = getSelectedIns();

    if (ins.length > 0) {
        if (ins[0] == "1") {
            return DMPConnect.Gender.M;
        }
        else if (ins[0] == "2") {
            return DMPConnect.Gender.Mme;
        }
    }
    return DMPConnect.Gender.UnknownGender;
}

function onConfidentialityLevelChanged()
{
    var enable = document.getElementById("enableSecretConnection").checked ? 1 : 0 ;

    dmpConnectInstance.hl_setConfidentialityLevel( enable , function (a) 
    {
        if( a.error ) 
        {
            appendLog("hl_setConfidentialityLevel failed: ", a );
        }
        else 
        {
            appendLog("hl_setConfidentialityLevel succeeded. ", a );
        }
    });
}

function getDocumentHandle()
{
    return getHandle( document.getElementById( 'hdocId' ) );
}

function getLocalPatientRootOid()
{
    return getFormTextFieldValue( document.getElementById( "localPatientRootOid" ) );
}

function getLocalPatientId()
{
    return getFormTextFieldValue( document.getElementById( "localPatientId" ) );
}

// Given a valid result of hl_getDmpAdministrativeData, fills the HTML fields matching the administrative data.
function fillAdministrativeData( aDmpAdministrativeDataStructure )
{
    var PatientData                     = aDmpAdministrativeDataStructure.PatientData;
    var patientExtendedCivilStatus      = PatientData.ExtendedCivilStatus;
    var patientCivilStatus              = patientExtendedCivilStatus.CivilStatus;
    var patientExtendedContactDetails   = PatientData.ExtendedContactDetails;
    var PatientOppositionsStatus        = aDmpAdministrativeDataStructure.PatientOppositions;
    var GuardianData                    = aDmpAdministrativeDataStructure.GuardianData;
    var GuardianCivilStatus             = GuardianData.CivilStatus;
    var GuardianContactDetails          = GuardianData.ContactDetails;

    // Patient civil status.
    setTextValue      ( "dmpPatientName"                 , patientCivilStatus.s_name                                         );
    setTextValue      ( "dmpPatientGiven"                , patientCivilStatus.s_given                                        );
    setSelectionValue ( "dmpPatientGender"               , patientCivilStatus.i_gender                                       );
    setSelectionValue ( "dmpPatientSex"                  , patientExtendedCivilStatus.i_sex                                  );
    setTextValue      ( "dmpPatientBirthDate"            , patientExtendedCivilStatus.s_birthDay                             );
    setTextValue      ( "dmpPatientBirthCountry"         , patientExtendedCivilStatus.s_birthCountry                         );
    setTextValue      ( "dmpPatientBirthName"            , patientExtendedCivilStatus.s_birthName                            );
    // Patient contact details.
    setTextValue      ( "dmpPatientAddress"              , patientExtendedContactDetails.ContactDetails.s_address            );
    setTextValue      ( "dmpPatientAddressComplement"    , patientExtendedContactDetails.ContactDetails.s_addressComplement  );
    setTextValue      ( "dmpPatientPostalCode"           , patientExtendedContactDetails.ContactDetails.s_postalCode         );
    setTextValue      ( "dmpPatientCity"                 , patientExtendedContactDetails.ContactDetails.s_city               );
    setTextValue      ( "dmpPatientCountry"              , patientExtendedContactDetails.s_country                           );
    setTextValue      ( "dmpPatientLandlinePhone"        , patientExtendedContactDetails.ContactDetails.s_landlinePhone      );
    setTextValue      ( "dmpPatientMobilePhone"          , patientExtendedContactDetails.ContactDetails.s_mobilePhone        );
    setTextValue      ( "dmpPatientEmail"                , patientExtendedContactDetails.ContactDetails.s_mail               );
    // Patient oppositions status.
    setCheckBoxState  ( "dmpPatientBrisDeGlaceOpposition", PatientOppositionsStatus.i_brisDeGlaceOpposition != 0             );
    setCheckBoxState  ( "dmpPatientCentre15Opposition"   , PatientOppositionsStatus.i_centre15Opposition != 0                );
    // Patient guardian.
    setCheckBoxState  ( "dmpPatientHasGuardian"          , aDmpAdministrativeDataStructure.i_guardian   != 1                 );
    setSelectionValue ( "dmpGuardianRole"                , GuardianData.i_role                                               );
    // Guardian civil status.
    setTextValue      ( "dmpGuardianName"                , GuardianCivilStatus.s_name                                        );
    setTextValue      ( "dmpGuardianGiven"               , GuardianCivilStatus.s_given                                       );
    setSelectionValue ( "dmpGuardianGender"              , GuardianCivilStatus.i_gender                                      );
    // Guardian contact details.
    setTextValue      ( "dmpGuardianAddress"             , GuardianContactDetails.s_address                                  );
    setTextValue      ( "dmpGuardianAddressComplement"   , GuardianContactDetails.s_addressComplement                        );
    setTextValue      ( "dmpGuardianPostalCode"          , GuardianContactDetails.s_postalCode                               );
    setTextValue      ( "dmpGuardianCity"                , GuardianContactDetails.s_city                                     );
    setTextValue      ( "dmpGuardianLandlinePhone"       , GuardianContactDetails.s_landlinePhone                            );
    setTextValue      ( "dmpGuardianMobilePhone"         , GuardianContactDetails.s_mobilePhone                              );
    setTextValue      ( "dmpGuardianEmail"               , GuardianContactDetails.s_mail                                     );
}

function updateAdministrativeDataFromFields( aDmpAdministrativeDataStructure )
{
    var PatientData                   = aDmpAdministrativeDataStructure.PatientData
    var patientExtendedCivilStatus    = PatientData.ExtendedCivilStatus;
    var patientCivilStatus            = patientExtendedCivilStatus.CivilStatus;
    var patientExtendedContactDetails = PatientData.ExtendedContactDetails;
    var PatientOppositionsStatus      = aDmpAdministrativeDataStructure.PatientOppositions
    var GuardianData                  = aDmpAdministrativeDataStructure.GuardianData 
    var GuardianCivilStatus           = GuardianData.CivilStatus
    var GuardianContactDetails        = GuardianData.ContactDetails

    // Patient civil status. 
    patientCivilStatus.s_name                                        = getTextValue( "dmpPatientName" );
    patientCivilStatus.s_given                                       = getTextValue( "dmpPatientGiven" );
    patientCivilStatus.i_gender                                      = parseInt( getSelectionValue( "dmpPatientGender" ) );
    patientExtendedCivilStatus.i_sex                                 = parseInt( getSelectionValue( "dmpPatientSex" ) );
    patientExtendedCivilStatus.s_birthDay                            = getTextValue( "dmpPatientBirthDate" );
    patientExtendedCivilStatus.s_birthCountry                        = getTextValue( "dmpPatientBirthCountry" );
    patientExtendedCivilStatus.s_birthName                           = getTextValue( "dmpPatientBirthName" );
    // Patient contact details.
    patientExtendedContactDetails.ContactDetails.s_address           = getTextValue( "dmpPatientAddress" );
    patientExtendedContactDetails.ContactDetails.s_addressComplement = getTextValue( "dmpPatientAddressComplement" );
    patientExtendedContactDetails.ContactDetails.s_postalCode        = getTextValue( "dmpPatientPostalCode" );
    patientExtendedContactDetails.ContactDetails.s_city              = getTextValue( "dmpPatientCity" );
    patientExtendedContactDetails.s_country                          = getTextValue( "dmpPatientCountry" );
    patientExtendedContactDetails.ContactDetails.s_landlinePhone     = getTextValue( "dmpPatientLandlinePhone" );
    patientExtendedContactDetails.ContactDetails.s_mobilePhone       = getTextValue( "dmpPatientMobilePhone" );
    patientExtendedContactDetails.ContactDetails.s_mail              = getTextValue( "dmpPatientEmail" );
    // Patient oppositions status.
    PatientOppositionsStatus.i_brisDeGlaceOpposition                 = getCheckBoxState( "dmpPatientBrisDeGlaceOpposition" ) ? 1 : 0 
    PatientOppositionsStatus.i_centre15Opposition                    = getCheckBoxState( "dmpPatientCentre15Opposition" ) ? 1 : 0 

    // Patient guardian
    var previouslyHadAGuardian = aDmpAdministrativeDataStructure.i_guardian == 2;
    var nowHaveAGuardian       = getCheckBoxState( "dmpPatientHasGuardian" );
    var guardianAction  = (previouslyHadAGuardian == nowHaveAGuardian) ? DMPConnect.GuardianAction.IgnoreGuardianSetup : 
        nowHaveAGuardian ? DMPConnect.GuardianAction.AddGuardian : DMPConnect.GuardianAction.RemoveGuardian
    aDmpAdministrativeDataStructure.i_guardian = guardianAction ;

    GuardianData.i_role                         = parseInt( getSelectionValue( "dmpGuardianRole" ) );
    // Guardian civil status. 
    GuardianCivilStatus.s_name                  = getTextValue( "dmpGuardianName" );
    GuardianCivilStatus.s_given                 = getTextValue( "dmpGuardianGiven" );
    GuardianCivilStatus.i_gender                = parseInt( getSelectionValue( "dmpGuardianGender" ) );
    // Guardian contact details.
    GuardianContactDetails.s_address            = getTextValue( "dmpGuardianAddress" );
    GuardianContactDetails.s_addressComplement  = getTextValue( "dmpGuardianAddressComplement" );
    GuardianContactDetails.s_postalCode         = getTextValue( "dmpGuardianPostalCode" );
    GuardianContactDetails.s_city               = getTextValue( "dmpGuardianCity" );
    GuardianContactDetails.s_landlinePhone      = getTextValue( "dmpGuardianLandlinePhone" );
    GuardianContactDetails.s_mobilePhone        = getTextValue( "dmpGuardianMobilePhone" );
    GuardianContactDetails.s_mail               = getTextValue( "dmpGuardianEmail" );
}

// ---------------------------------------------------------------------------------------------------------------------------------

function searchDocumentsTest()
{
    var filterCriterions =
        {
            "i_visibility" : DMPConnect.DocumentVisibility.Normal,
            "Categories" :
            {
                "#0" : "11505-5",
                "#1" : "11488-4"
            },
            "s_creationDateBottom" : "2014"
        }

    var optionalParams =
    {
        "s_localPatientRootOid" : getLocalPatientRootOid()
    };

    var proxyServer   = getTextValue( "sessionProxyServer" );
    var proxyPort     = getTextValue( "sessionProxyPort" );
    var proxyLogin    = getTextValue( "sessionProxyUsername" );
    var proxyPassword = getTextValue( "sessionProxyPassword" );

    var applicationId = getTextValue( "applicationId" );

    if( proxyServer.length > 0 && proxyPort.length > 0 )
    {
        iPort = parseInt( proxyPort , 10 );

        if( ! isNaN( iPort ) )
        {
            optionalParams.s_proxyIpOrUrl  = proxyServer;
            optionalParams.i_proxyPort     = iPort;
            optionalParams.s_proxyLogin    = proxyLogin;
            optionalParams.s_proxyPassword = proxyPassword;
        }
    }

    // dmp Connector proxy 
    proxyServer   = getTextValue( "dmpProxyServer" );
    proxyPort     = getTextValue( "dmpProxyPort" );
    proxyLogin    = getTextValue( "dmpProxyUsername" );
    proxyPassword = getTextValue( "dmpProxyPassword" );

    if( proxyServer.length > 0 && proxyPort.length > 0 )
    {
        iPort = parseInt( proxyPort , 10 );

        if( ! isNaN( iPort ) )
        {
            optionalParams.s_proxyIpOrUrl  = proxyServer;
            optionalParams.i_proxyPort     = iPort;
            optionalParams.s_proxyLogin    = proxyLogin;
            optionalParams.s_proxyPassword = proxyPassword;
        }
    }

    if( applicationId.length > 0 )
    {
        if( optionalParameters == undefined )
        {
            optionalParameters = {};
        }

        optionalParameters.s_applicationId = applicationId;
    }

    dmpConnectInstance.hl_openSession(3600, function( a ) {
        dmpConnectInstance.hl_getCpxCard( getCpxReaderIndex(), getCpxReaderName(), function( a ) {
            dmpConnectInstance.hl_createDmpConnector( getCpsPinCode(), getDmpServerName(), 60, 'AMBULATOIRE', 0, optionalParams, function( a ) {
                dmpConnectInstance.hl_findDocuments( getSelectedIns(), filterCriterions, function( a ) {
                    dmpConnectInstance.hl_closeSession(function( a ) {
                        if (a.error) {
                            appendLog("-- searchDocumentsTest failed: ", a);
                        } else {
                            appendLog("-- searchDocumentsTest succeeded.", a );
                        }
                    });
                });
            });
        });
    });
}

function initDmpConnector()
{
    optionalParameters = undefined;

    var proxyServer   = getTextValue( "sessionProxyServer" );
    var proxyPort     = getTextValue( "sessionProxyPort" );
    var proxyLogin    = getTextValue( "sessionProxyUsername" );
    var proxyPassword = getTextValue( "sessionProxyPassword" );

    var applicationId = getTextValue( "applicationId" );

    if( proxyServer.length > 0 && proxyPort.length > 0 )
    {
        iPort = parseInt( proxyPort , 10 );

        if( ! isNaN( iPort ) )
        {
            optionalParameters = {};

            optionalParameters.s_proxyIpOrUrl  = proxyServer;
            optionalParameters.i_proxyPort     = iPort;
            optionalParameters.s_proxyLogin    = proxyLogin;
            optionalParameters.s_proxyPassword = proxyPassword;
        }
    }

    if( applicationId.length > 0 )
    {
        if( optionalParameters == undefined )
        {
            optionalParameters = {};
        }

        optionalParameters.s_applicationId = applicationId;
    }

    dmpConnectInstance.hl_openSession(3600, optionalParameters, function( a )
    {
        dmpConnectInstance.hl_getCpxCard( getCpxReaderIndex(), getCpxReaderName(), function( a )
        {
            var optionalParamsNtpServer = {
              "s_ntpIpOrUrl" : "127.0.0.1",
              "i_ntpPort" : 8888
            };

            // Set it to either optionalParamsHttpProxy or optionalParamsNtpServer or both to use an HTTP proxy or
            //  a custom NTP server.
            var optionalLocalRootOid =
            {
                "s_localPatientRootOid" : getLocalPatientRootOid()
            };

            var optionalParams = optionalLocalRootOid;

            if( document.getElementById( "dmpProxyServer" ) != undefined )
            {
                var proxyServer     = getTextValue("dmpProxyServer");
                var proxyPort       = getTextValue("dmpProxyPort");
                var proxyLogin      = getTextValue("dmpProxyUsername");
                var proxyPassword   = getTextValue("dmpProxyPassword");

                if (proxyServer.length > 0 && proxyPort.length > 0) {
                    iPort = parseInt(proxyPort, 10);

                    if (!isNaN(iPort)) {
                        optionalParams.s_proxyIpOrUrl   = proxyServer;
                        optionalParams.i_proxyPort      = iPort;
                        optionalParams.s_proxyLogin     = proxyLogin;
                        optionalParams.s_proxyPassword  = proxyPassword;
                    }
                }
            }


            // Set all the practice locations 
            dmpConnectInstance.hl_readCpxCard( getCpsPinCode() , function(a)
            {
                if( ! a.error )
                {
                    fillPracticeLocationList( a ) ;
                }
            });

            dmpConnectInstance.hl_createDmpConnector( getCpsPinCode(), getDmpServerName(), 60, 'AMBULATOIRE', 0, optionalParams, function( a )
            {
                if( a.error )
                {
                  appendLog( "-- initDmpConnector failed: ", a );
                }
                else
                {
                  appendLog( "-- initDmpConnector succeeded.", a );
                }
            } );
        } );
    } );
}



// ---------------------------------------------------------------------------------------------------------------------------------

function hl_createDmpConnector()
{
    var optionalLocalRootOid =
    {
        "s_localPatientRootOid" : getLocalPatientRootOid()
    };

    var optionalParams = optionalLocalRootOid;

    if( document.getElementById( "dmpProxyServer" ) != undefined )
    {
        var proxyServer   = getTextValue("dmpProxyServer");
        var proxyPort     = getTextValue("dmpProxyPort");
        var proxyLogin    = getTextValue("dmpProxyUsername");
        var proxyPassword = getTextValue("dmpProxyPassword");
    
        if (proxyServer.length > 0 && proxyPort.length > 0) 
        {
            iPort = parseInt(proxyPort, 10);

            if (!isNaN(iPort)) 
            {
                optionalParams.s_proxyIpOrUrl  = proxyServer;
                optionalParams.i_proxyPort     = iPort;
                optionalParams.s_proxyLogin    = proxyLogin;
                optionalParams.s_proxyPassword = proxyPassword;
            }
        }
    }

    dmpConnectInstance.hl_createDmpConnector( getCpsPinCode(), getDmpServerName(), 60, 'AMBULATOIRE', 0, optionalParams, function(a) 
    {
        if (a.error) {
            appendLog("-- hl_createDmpConnector failed: ", a);
        } else {
            appendLog("-- hl_createDmpConnector succeeded.", a );
        }
    });
}



function hl_getCertifiedIdentity()
{
    var nirOD       = getFormTextFieldValue( document.getElementById('TD00_nirOD_in') );
    var birthDate   = getFormTextFieldValue( document.getElementById('TD00_birthDate_in') );
    var birthRank   = getFormTextFieldValue( document.getElementById('TD00_birthRank_in') );
    var nirIndividu = getFormTextFieldValue( document.getElementById('TD00_nirIndividu_in') );
    
    dmpConnectInstance.hl_getCertifiedIdentity( nirOD, birthDate, birthRank, nirIndividu , function (a) {
        if( a.error )
        {
            appendLog( "-- hl_getCertifiedIdentity failed: ", a );
        }
        else 
        {
            appendLog( "-- hl_getCertifiedIdentity succeed.", a );
            
            setTextValue( "TD00_name_out"       , a.s_patientName );
            setTextValue( "TD00_given_out"      , a.s_patientGivenName );
            setTextValue( "TD00_birthDate_out"  , a.s_birthday );
            
            if( a.Ins.s_insType == "N" )
                setTextValue( "TD00_nirIndividu_out", a.Ins.s_ins );
            else 
                setTextValue( "TD00_nirIndividu_out", a.Ins.s_ins + a.Ins.s_insType );

        }
    } );
}

function hl_getDirectAuthenticationDMPStatus()
{
    dmpConnectInstance.hl_getDirectAuthenticationDMPStatus( getSelectedIns(), function(a) {
        if (a.error) {
            appendLog("-- hl_getDirectAuthenticationDMPStatus failed: ", a);
        } else {
            appendLog("-- hl_getDirectAuthenticationDMPStatus succeeded.", a );
        }
    });
}



function hl_findDocuments( getMetadata )
{
    var filterCriterions =
        {
            "i_visibility" : DMPConnect.DocumentVisibility.Normal,
            "s_creationDateBottom" : "2014",
            "i_status" : DMPConnect.DocumentStatus.Archived + DMPConnect.DocumentStatus.Approved
            //"i_status" : DMPConnect.DocumentStatus.Archived + DMPConnect.DocumentStatus.Approved + DMPConnect.DocumentStatus.Deprecated
            //"i_status" : DMPConnect.DocumentStatus.Deprecated
        }

    if( !getMetadata )
    {
        filterCriterions.i_disableMetadataSearch = 1;
    }

    dmpConnectInstance.hl_findDocuments( getSelectedIns(), filterCriterions, function(a) {
        if (a.error) {
            appendLog("-- hl_findDocuments failed: ", a);
        } else {
            appendLog("-- hl_findDocuments succeeded.", a );
        }
    });
}

/**
  * @param {number} treatingPhysician 1: enable "Medecin Traitant" access to the given DMP. Ignored if
  *                                   action == RemoveAuthorization
  */
function hl_updateUserDmpAccessAuthorization( action, treatingPhysician )
{
    dmpConnectInstance.hl_updateUserDmpAccessAuthorization( getSelectedIns(), action, treatingPhysician, null, function(a) {
        if (a.error) {
            appendLog("-- hl_updateUserDmpAccessAuthorization failed: ", a);
        } else {
            appendLog("-- hl_updateUserDmpAccessAuthorization succeeded.", a );
        }
    });
}

function hl_updateUserDmpAccessAuthorization_Add()
{
    hl_updateUserDmpAccessAuthorization( DMPConnect.UserAuthorizationAction.AddAuthorization, 0 );
}

function hl_updateUserDmpAccessAuthorization_AddMT()
{
    hl_updateUserDmpAccessAuthorization( DMPConnect.UserAuthorizationAction.AddAuthorization, 1 );
}

function hl_updateUserDmpAccessAuthorization_RmMT()
{
    hl_updateUserDmpAccessAuthorization( DMPConnect.UserAuthorizationAction.AddAuthorization, 0 );
}

function hl_updateUserDmpAccessAuthorization_Remove()
{
    hl_updateUserDmpAccessAuthorization( DMPConnect.UserAuthorizationAction.RemoveAuthorization, 0 );
}

function hl_getAccessibleDMPList()
{
    dmpConnectInstance.hl_getAccessibleDMPList("LASTAUTORIZATION", "20100101", null, function(a) {
        if (a.error) {
            appendLog("-- hl_getAccessibleDMPList failed: ", a);
        } else {
            appendLog("-- hl_getAccessibleDMPList succeeded.", a );
        }
    });
}

function hl_getDocumentContent()
{
    dmpConnectInstance.hl_getDocumentContent( getDocumentHandle(), null, function(a) {
        if (a.error) 
        {
            appendLog("-- hl_getDocumentContent failed: ", a);
        } 
        else 
        {
            document.getElementById("documentHeaders").value = decodeURIComponent(escape(window.atob((a.s_cdaHeadersInBase64))));
            appendLog("-- hl_getDocumentContent succeeded.", a );
        }
    });
}

function hl_getDocumentContentByUniqueId()
{
    dmpConnectInstance.hl_getDocumentContentByUniqueId(getSelectedIns(), getDocumentUniqueId(), getDocumentUuid(), null, function (a) 
    {
        if (a.error) {
            appendLog("-- hl_getDocumentContentByUniqueId failed: ", a);
        }
        else {
            document.getElementById("documentHeaders").value = decodeURIComponent(escape(window.atob((a.s_cdaHeadersInBase64))));
            appendLog("-- hl_getDocumentContentByUniqueId succeeded.", a);
        }
    });
}


function hl_updateDocumentVisibility()
{
    var selector      = document.getElementById( "newVisibility" );
    var newVisibility = selector.value;

    dmpConnectInstance.hl_updateDocumentVisibility( getDocumentHandle(), "SA07", newVisibility, null, function(a){
        if (a.error) {
            appendLog("-- hl_updateDocumentVisibility failed: ", a);
        } else {
            appendLog("-- hl_updateDocumentVisibility succeeded.", a );
        }
    });
}

function hl_updateDocumentVisibilityByUniqueId()
{
    var selector      = document.getElementById( "newVisibility" );
    var newVisibility = selector.value;

    dmpConnectInstance.hl_updateDocumentVisibilityByUniqueId(getSelectedIns(), getDocumentUniqueId(), getDocumentUuid(), "SA07", newVisibility, null, function (a) {
        if (a.error) {
            appendLog("-- hl_updateDocumentVisibilityByUniqueId failed: ", a);
        }
        else {
            appendLog("-- hl_updateDocumentVisibilityByUniqueId succeeded.", a);
        }
    });
}

function hl_updateDocumentStatus()
{
    dmpConnectInstance.hl_updateDocumentStatus( getDocumentHandle(), "SA07", null, function(a) {
        if (a.error) {
            appendLog("-- hl_updateDocumentStatus failed: ", a);
        } else {
            appendLog("-- hl_updateDocumentStatus succeeded.", a );
        }
    });
}

function hl_updateDocumentStatusByUniqueId()
{
    dmpConnectInstance.hl_updateDocumentStatusByUniqueId( getSelectedIns(), getDocumentUniqueId(), getDocumentUuid(), "SA07", null, function(a) 
    {
        if (a.error) {
            appendLog("-- hl_updateDocumentStatusByUniqueId failed: ", a);
        }
        else {
            appendLog("-- hl_updateDocumentStatusByUniqueId succeeded.", a);
        }
    });
}

function hl_deleteDocument()
{
    var documentUid = getFormTextFieldValue( document.getElementById('hdocUid') );

    dmpConnectInstance.hl_deleteDocument(documentUid, getSelectedIns(), "SA07", null, function(a) {
        if (a.error) {
            appendLog("-- hl_deleteDocument failed: ", a);
        } else {
            appendLog("-- hl_deleteDocument succeeded.", a );
        }
    });
}

function hl_sendDocument( content /* Raw base 64 file data. */ , 
                          documentTitle,
                          documentDescription,
                          documentFormat , 
                          documentCategory ,
                          documentEventCodes ,
                          documentInformants,
                          documentTreatingPhysician,
                          documentVisibility,
                          documentAdditionalAuthors )
{
    var eventCodes = null;
    if( documentEventCodes.length > 0 )
    {
        eventCodes = documentEventCodes
    };
    var optionalParams =
    {
        "s_localPatientId" : getLocalPatientId(),
        "Informants"       : documentInformants
    };
    if( documentTreatingPhysician != undefined )
    {
        optionalParams.TreatingPhysician = documentTreatingPhysician;
    }
    if( documentAdditionalAuthors.length > 0 )
    {
        optionalParams.AdditionalAuthors = documentAdditionalAuthors
    }
    //
    // In order to perform automatic of typecode, uncomment the following line.
    // optionalParams.i_transcodeTypeCode = 1
    // 

    // In order to perform schematrons validation for unstructured documents uncomment the following line.
    // optionalParams.i_forceSchematronsValidation = 1


    dmpConnectInstance.hl_sendDocument( getSelectedIns(), content,
                                        documentTitle,              // Document title
                                        documentDescription,        // Document description
                                        documentCategory,           // Ex. "46241-6" / 'CR d'admission'
                                        documentVisibility,         // Ex. DMPConnect.DocumentVisibility.Normal
                                        documentFormat,             // Ex. DMPConnect.DocumentFormat.PlainText
                                        "SA07",                     // 'Cadre de soins'
                                        null,
                                        getSubmissionSetTitle(),
                                        getSubmissionSetDescription(),
                                        eventCodes, 
                                        optionalParams,
                                        function(a) {
        if (a.error) {
            appendLog("-- hl_sendDocument failed: ", a);
        } else {
            appendLog("-- hl_sendDocument succeeded.", a );
        }
    });
}

function hl_sendDocuments( documents )
{
    var optionalParams = { };
    //
    // In order to perform automatic of typecode on each document(s), uncomment the following line.
    // optionalParams.i_transcodeTypeCode = 1
    // 
    // In order to perform schematrons validation for unstructured documents uncomment the following line.
    // optionalParams.i_forceSchematronsValidation = 1


    dmpConnectInstance.hl_sendDocuments( getSelectedIns() ,
                                         "SA07",
                                         getSubmissionSetTitle(),
                                         getSubmissionSetDescription(),
                                         getLocalPatientId(),
                                         documents,
                                         optionalParams,
                                         function (a) {
            if (a.error) {
                appendLog("-- hl_sendDocuments failed:", a);
            }
            else {
                appendLog("-- hl_sendDocuments succeeded.", a);
            }
        });
}

function hl_getDmpAdministrativeData()
{
    dmpConnectInstance.hl_getDmpAdministrativeData( getSelectedIns(), null, function(a) {
        if (a.error) {
            appendLog("-- hl_getDmpAdministrativeData failed: ", a);
        } else {
            appendLog("-- hl_getDmpAdministrativeData succeeded.", a );

            fillAdministrativeData( a );
        }
    });
}

function hl_updateDmpAdministrativeData()
{
    dmpConnectInstance.hl_getDmpAdministrativeData( getSelectedIns(), null , function(a) 
    {
        updateAdministrativeDataFromFields( a ) ;
        dmpConnectInstance.hl_updateDmpAdministrativeData( a , function( b ) {
            if( a.error ) {
                appendLog( "-- hl_updateDmpAdministrativeData failed: ", b);
            } else {
                appendLog( "-- hl_updateDmpAdministrativeData succeeded." , b );
            }
        });
    });
}

function hl_createDmp()
{
    var dmpAdministrativeData =
    {
        PatientData :
        {
            // Use either an Ins structure or s_ins field to define the INS of the patient.
            /*
            Ins:
            {
                s_ins : getSelectedIns() ,
                s_insType : "N"
            },
            */
            s_ins : getSelectedIns(),
            ExtendedCivilStatus :
            {
                CivilStatus :
                {
                    i_gender : DMPConnect.Gender.M,
                    s_name   : "NAMETESTDMPCJS",
                    s_given  : "GIVENTESTDMPCJS"
                },
                s_birthName    : "",
                s_birthDay     : getCurrentVitalePatientRawBirthdayYYYYMMDD(), // YYYYMMDD
                s_birthCountry : "France",
                i_sex          : DMPConnect.Sex.Male
            },
            ExtendedContactDetails :
            {
                ContactDetails :
                {
                    s_mobilePhone       : "0601010101",
                    s_landlinePhone     : "0501010101",
                    s_mail              : "igor@igor.net",
                    s_address           : "Au fond à gauche",
                    s_addressComplement : "Chez Michou",
                    s_postalCode        : "77000",
                    s_city              : "Parmont"
                },
                s_country : "France"
            }
        },
        PatientOppositions :
        {
            i_brisDeGlaceOpposition : 0,
            i_centre15Opposition    : 1
        },
        //i_guardian : DMPConnect.GuardianStatus.NoGuardianDefined,
        i_guardian : DMPConnect.GuardianStatus.GuardianDefined,
        GuardianData :
        {
            i_role : DMPConnect.LegalRepresentantRole.GrandGrandMother,
            CivilStatus :
            {
                i_gender : DMPConnect.Gender.Mme,
                s_name   : "AATIUN",
                s_given  : "Igorina"
            },
            ContactDetails :
            {
                s_mobilePhone       : "0633333333",
                s_landlinePhone     : "0511111111",
                s_mail              : "igorina@igor.net",
                s_address           : "Lieu dit Chez Igor",
                s_addressComplement : "BP 20654",
                s_postalCode        : "98452",
                s_city              : "Eparvaux"
            }
        }
    };
    var vitaleData =
    {
        s_name     : getCurrentVitalePatientName(),
        s_birthName: getCurrentVitalePatientBirthName(),
        s_given    : getCurrentVitalePatientGiven() ,
        s_birthDay : getCurrentVitalePatientBirthdayYYMMDD() // YYMMDD
    };
    dmpConnectInstance.hl_createDmp( dmpAdministrativeData,
                                     vitaleData,
                                     null,
                                     function( a ) {
        if (a.error) {
            appendLog("-- hl_createDmp failed: ", a);
        } else {
            appendLog("-- hl_createDmp succeeded.", a );
        }
    });
}

function hl_createDmpAndOtp()
{
    var ins = getFormTextFieldValue( getSelectedIns() );

    // Vitale data
    var vitaleData =
    {
        s_name      : getFormTextFieldValue( document.getElementById( "vitalePatientName"       ) ),
        s_birthName : getFormTextFieldValue( document.getElementById( "vitalePatientBirthName"  ) ),
        s_given     : getFormTextFieldValue( document.getElementById( "vitalePatientGiven"      ) ),
        s_birthDay  : getFormTextFieldValue( document.getElementById( "vitalePatientBirthday"   ) )
    };

    var dmpAdministrativeData =
    {
        PatientData :
        {
            // Use either an Ins structure or s_ins field to define the INS of the patient.
            /*
            Ins:
            {
                s_ins : getSelectedIns() ,
                s_insType : "N"
            },
            */
            s_ins : getSelectedIns(),
            ExtendedCivilStatus :
            {
                CivilStatus :
                {
                    i_gender : computeSelectedPatientGender(),
                    s_name   : getFormTextFieldValue( document.getElementById( "dmpPatientName"  ) ) ,
                    s_given  : getFormTextFieldValue( document.getElementById( "dmpPatientGiven" ) )
                },
                s_birthName    : getFormTextFieldValue( document.getElementById( "dmpPatientBirthName" ) ),
                s_birthDay     : getFormTextFieldValue( document.getElementById( "dmpPatientBirthDate" ) ), // YYYYMMDD
                s_birthCountry : getFormTextFieldValue( document.getElementById( "dmpPatientBirthCountry" ) ) ,
                i_sex          : DMPConnect.Sex.Male
            },
            ExtendedContactDetails :
            {
                ContactDetails :
                {
                    s_mobilePhone       : getFormTextFieldValue( document.getElementById( "dmpPatientMobilePhone"   ) ),
                    s_landlinePhone     : getFormTextFieldValue( document.getElementById( "dmpPatientLandlinePhone" ) ),
                    s_mail              : getFormTextFieldValue( document.getElementById( "dmpPatientEmail"         ) ),
                    s_address           : getFormTextFieldValue( document.getElementById( "dmpPatientAddress"       ) ),
                    s_addressComplement : getFormTextFieldValue( document.getElementById( "dmpPatientAddressComplement" ) ),
                    s_postalCode        : getFormTextFieldValue( document.getElementById( "dmpPatientPostalCode" ) ),
                    s_city              : getFormTextFieldValue( document.getElementById( "dmpPatientCity" ) )
                },
                s_country : getFormTextFieldValue( document.getElementById( "dmpPatientCountry" ) )
            }
        },
        PatientOppositions :
        {
            i_brisDeGlaceOpposition : 0,
            i_centre15Opposition    : 1
        },
        //i_guardian : DMPConnect.GuardianStatus.NoGuardianDefined,
        i_guardian : DMPConnect.GuardianStatus.GuardianDefined,
        GuardianData :
        {
            i_role : DMPConnect.LegalRepresentantRole.GrandGrandMother,
            CivilStatus :
            {
                i_gender : DMPConnect.Gender.Mme,
                s_name   : "AATIUN",
                s_given  : "Igorina"
            },
            ContactDetails :
            {
                s_mobilePhone       : "0633333333",
                s_landlinePhone     : "0511111111",
                s_mail              : "igorina@igor.net",
                s_address           : "Lieu dit Chez Igor",
                s_addressComplement : "BP 20654",
                s_postalCode        : "98452",
                s_city              : "Eparvaux"
            }
        }
    };

    var option =
    {
        i_returnAsFile : 1 
    };

    dmpConnectInstance.hl_createDmpAndOtp( dmpAdministrativeData,
                                           vitaleData,
                                           option,
        function (a) {
            if (a.error) {
                appendLog("-- hl_createDmpAndOtp failed: ", a);
            } else {
                appendLog("-- hl_createDmpAndOtp succeeded.", a);
            }
        });
}

function hl_createAcknowledgementPdf() 
{
    var ins          = getSelectedIns();
    var name         = getSelectedPatientName();
    var given        = getSelectedPatientGiven();
    var gender       = computeSelectedPatientGender();
    var returnAsFile = 1;
    var openPdf      = 0;

    dmpConnectInstance.hl_createAcknowledgementPdf(ins, gender, name, given, returnAsFile, openPdf, function (a) {
        if (a.error) {
            appendLog("-- hl_createAcknowledgementPdf failed: ", a);
        }
        else {
            appendLog("-- hl_createAcknowledgementPdf succeeded.", a);
        }
    });
}


function hl_closeDmp()
{
    dmpConnectInstance.hl_closeDmp( getSelectedIns(),
                                    "Raison pour laquelle le DMP est fermé.",
                                    "DUPONT",
                                    "Jean",
                                    null, function(a) {
        if (a.error) {
            appendLog("-- hl_closeDmp failed: ", a);
        } else {
            appendLog("-- hl_closeDmp succeeded.", a );
        }
    });
}

function hl_reactivateDmp()
{
    dmpConnectInstance.hl_reactivateDmp( getSelectedIns(), DMPConnect.Gender.M, "Hyppolite", "DUPONT",
                                         DMPConnect.Sex.Male, "Kazakhstan", "19020411",
                                         "DUPONT", "LD Parmont", "Au fond à gauche", "Eparvaux",
                                         "0123567845", "trugudu@plouf.com", "0601020304", "25000", "France",
                                         null, function(a) {
        if (a.error) {
            appendLog("-- hl_reactivateDmp failed: ", a);
        } else {
            appendLog("-- hl_reactivateDmp succeeded.", a );
        }
    });
}

function hl_findPatients()
{
    var inputName     = document.getElementById( 'findPatientName' ).value;
    var inputGiven    = document.getElementById( 'findPatientGiven' ).value;
    var inputCity     = document.getElementById( 'findPatientCity' ).value;
    var inputBirthday = document.getElementById( 'findPatientBirthday' ).value;
    var approxName    = document.getElementById( 'findPatientApproxName' ).checked;
    var approxCity    = document.getElementById( 'findPatientApproxCity' ).checked;

    var params = {
        s_name       : inputName, // Eg. "DESM"
        s_givenName  : inputGiven,
        s_birthday   : inputBirthday,
        s_city       : inputCity,
        s_postalCode : "",
        i_sex        : DMPConnect.Sex.UnknownSex,
        i_approxName : approxName ? 1 : 0,
        i_approxCity : approxCity ? 1 : 0
    };
    dmpConnectInstance.hl_findPatients( params, function(a) {
        if( a.error ){
            appendLog("-- hl_findPatients failed: ", a);
        } else {
            appendLog("-- hl_findPatients succeeded.", a );
        }
    });
}

function hl_setPracticeLocation()
{
    var selectedCpsPracticeLocationIndice = getSelectedPracticeLocationIndice();
    var selectedPracticeSetting           = getSelectedPracticeSettings();

    dmpConnectInstance.hl_setPracticeLocation( selectedPracticeSetting , selectedCpsPracticeLocationIndice , function(a)
    {
        if( a.error )
        {
            appendLog( "-- hl_setPracticeLocation failed: ", a );
        } else 
        {
            appendLog( "-- hl_setPracticeLocation succeeded." , a );
        }
    });
}

function hl_getInsNirFromInsC()
{
    var insc = getTextValue( "PatientINSC" );
    
    dmpConnectInstance.hl_getInsNirFromInsC( insc , function (a) 
    {
        if( a.error )
        {
            appendLog( "-- hl_getInsNirFromInsC failed: ", a );
        }
        else 
        {
            appendLog( "-- hl_getInsNirFromInsC succeeded." , a );

            setTextValue( "TD00_birthDate_out" , "" );
            setTextValue( "TD00_given_out"     , "" );
            setTextValue( "TD00_name_out"      , "" );

            if( a.Ins.s_insType == "N" )
                setTextValue( "TD00_nirIndividu_out", a.Ins.s_ins );
            else 
                setTextValue( "TD00_nirIndividu_out", a.Ins.s_ins + a.Ins.s_insType );
        }
    });
}


function hl_setDmpAccessModeBrisDeGlace()
{
    dmpConnectInstance.hl_setDmpAccessMode( DMPConnect.AccessMode.BrisDeGlace,
                                            "Patient en état de choc.",
                                            null,
                                            function(a) {
        if (a.error) {
            appendLog("-- hl_setDmpAccessModeBrisDeGlace failed: ", a);
        } else {
            appendLog("-- hl_setDmpAccessModeBrisDeGlace succeeded.", a );
        }
    });
}

function hl_getOtpChannelValue()
{
    // Get SMS
    dmpConnectInstance.hl_getOtpChannelValue(getSelectedIns(), 1,
        function (a) 
        {
            if (a.error) 
            {
                appendLog("-- hl_getOtpChannelValue failed: ", a);
            }
            else 
            {
                document.getElementById('otpSMS').value = a.s_otpChannelValue;
                appendLog("-- hl_getOtpChannelValue succeeded.", a);
            }
        });

    // Get Mail
    dmpConnectInstance.hl_getOtpChannelValue(getSelectedIns(), 2,
        function (a) 
        {
            if (a.error) 
            {
                appendLog("-- hl_getOtpChannelValue failed: ", a);
            }
            else 
            {
                document.getElementById('otpEmail').value = a.s_otpChannelValue;
                appendLog("-- s_otpChannelValue succeeded.", a);
            }
        });
}

function hl_getPatientWebAccessPdf()
{
    var sms   = document.getElementById('otpSMS'  ).value;
    var email = document.getElementById('otpEmail').value;
    dmpConnectInstance.hl_getPatientWebAccessPdf( getSelectedIns(), sms, email, 0, /* 1 to get the generated pdf as a file on the host computer.*/
                                                                                 1, /* 1 to open the PDF with the system default PDF viewer, if possible. */
        function( a ){
            if( a.error ) {
                appendLog("-- hl_getPatientWebAccessPdf failed: ", a );
            } else {
                appendLog("-- hl_getPatientWebAccessPdf succeeded.", a );
            }
        }
    );
}

function hl_getWebPsDmpUrls()
{
    dmpConnectInstance.hl_getWebPsDmpUrls( getSelectedIns(),
        function( a ){
            if( a.error ) {
                appendLog("-- hl_getWebPsDmpUrls failed: ", a );
            } else {
                appendLog("-- hl_getWebPsDmpUrls succeeded.", a );
            }
        }
    );
}

function hl_setWebPsRootUrl()
{
    var webPsRootUrl = document.getElementById("webPsRootUrl").value;

    dmpConnectInstance.hl_setWebPsRootUrl( webPsRootUrl ,
        function( a ) {
            if( a.error ) {
                appendLog("-- hl_setWebPsRootUrl failed: ", a );
            }
            else {
                appendLog("-- hl_setWebPsRootUrl succeeded." , a );
            }
        }
    );
}

function hl_getDmpParameters()
{
    dmpConnectInstance.hl_getDmpParameters(function (a) {
        if (a.error) {
            appendLog("-- hl_getDmpParameters failed: ", a);
        }
        else {
            appendLog("-- hl_getDmpParameters succeeded.", a);
        }
    }
    );
}

function hl_getMajorityAge() 
{
    dmpConnectInstance.hl_getMajorityAge(function (a) {
        if (a.error) {
            appendLog("-- hl_getMajorityAge failed: ", a);
        }
        else {
            appendLog("-- hl_getMajorityAge succeeded.", a);
        }
    }
    );
}


function hl_getDmpAuthorizationsList() 
{
    dmpConnectInstance.hl_getDmpAuthorizationsList(getSelectedIns(), 1, function (a) {
        if (a.error) {
            appendLog("-- hl_getDmpAuthorizationsList failed: ", a);
        }
        else {
            appendLog("-- hl_getDmpAuthorizationsList succeeded.", a);
        }
    });
}
