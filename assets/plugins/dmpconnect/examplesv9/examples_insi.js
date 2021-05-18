function populateInsiResult( aResult )
{
    if( aResult.i_insIdentityResult == DMPConnect.InsIdentityResult.InsIdentityResult_SingleIdentityFound )
    {
        var ident       = aResult.Identity ;
        var ins         = ident.Ins ;

        var name        = ident.s_birthName ;
        var given       = ident.s_given ;
        var birthGiven  = ident.s_birthGiven ;
        var sex         = ident.i_sex ;
        var date        = ident.s_birthDate ;
        var place       = ident.s_birthPlace ;

        var insValue     = ins.s_value ; 
        var insKey       = ins.s_key ;
        var insOid       = ins.s_oid ;
        var insStartDate = ins.s_startDate ;
        var insEndDate   = ins.s_endDate ;

        setTextValue( "insiResultBirthName", name );
        setTextValue( "insiResultGiven" , given );
        setTextValue( "insiResultBirthGiven" , birthGiven );
        setTextValue( "insiResultSex" , sex );
        setTextValue( "insiResultBirthDate", date );
        setTextValue( "insiResultBirthPlace" , place );

        setTextValue( "insiResultCurrentInsValue", insValue );
        setTextValue( "insiResultCurrentInsKey", insKey );
        setTextValue( "insiResultCurrentInsOid" , insOid );
        setTextValue( "insiResultCurrentInsStartDate", insStartDate );
        setTextValue( "insiResultCurrentInsEndDate" , insEndDate );
    }
}

function hl_createInsiConnector() 
{
    var connectionDomain = document.getElementById("insiServerUrl").value;

    dmpConnectInstance.hl_createInsiConnector(getCpsPinCode(), connectionDomain, 60, 'AMBULATOIRE', 0, null, function (a) {
        if (a.error) {
            appendLog("-- hl_createInsiConnector failed: ", a);
        } else {
            appendLog("-- hl_createInsiConnector succeeded.", a);
        }
    });
}


function hl_getInsFromVitaleCard()
{
    var LpsInfos =
    {
        s_idam: document.getElementById("AuthorizationNumber").value,
        s_numAM         : "04",
        s_version       : "1",
        s_instance      : "550e8400-e29b-41d4-a716-446655440000",
        s_name          : "INSICONNECT",
        s_billingNumber : document.getElementById("BillingNumber").value
    };

    var indexElt = document.getElementById( "vitalePatientIndex" );
    var index = parseInt( indexElt.value );

    dmpConnectInstance.hl_getInsFromVitaleCard( LpsInfos, index , function (e) 
    {
        if( e.error )
        {
            appendLog( "-- hl_getInsFromVitaleCard failed." , e );
        }
        else 
        {
            appendLog( "-- hl_getInsFromVitaleCard succeeded." , e );
            populateInsiResult( e );
        }
    });
}

function hl_getInsFromIdentityInformation()
{
    var LpsInfos =
    {
        s_idam          : document.getElementById("AuthorizationNumber").value,
        s_numAM         : "04",
        s_version       : "1",
        s_instance      : "550e8400-e29b-41d4-a716-446655440000",
        s_name          : "INSICONNECT",
        s_billingNumber : document.getElementById("BillingNumber").value
    };

    var nameElt  = document.getElementById( "insiPatientName" );
    var givenElt = document.getElementById( "insiPatientGiven" );
    var sexElt   = document.getElementById( "insiPatientSex" );
    var dateElt  = document.getElementById( "insiPatientBirthDate" );
    var placeElt = document.getElementById( "insiPatientBirthPlace" );

    var name  = nameElt.value;
    var given = givenElt.value;
    var sex   = parseInt( sexElt.value );
    var date  = dateElt.value;
    var place = placeElt.value;

    dmpConnectInstance.hl_getInsFromIdentityInformation( LpsInfos, name, given, sex, date, place, function (e)
    {
        if( e.error )
        {
            appendLog( "-- hl_getInsFromIdentityInformation failed." , e );
        }
        else 
        {
            appendLog( "-- hl_getInsFromIdentityInformation succeeded." , e );            
            populateInsiResult( e );
        }
    });
}

function hl_checkInsIdentity()
{
    var LpsInfos =
    {
        s_idam          : document.getElementById("AuthorizationNumber").value,
        s_numAM         : "04",
        s_version       : "1",
        s_instance      : "550e8400-e29b-41d4-a716-446655440000",
        s_name          : "INSICONNECT",
        s_billingNumber : document.getElementById("BillingNumber").value
    };

    var ins   = document.getElementById( "insiCheckIns").value;
    var key   = document.getElementById( "insiCheckKey").value;
    var oid   = document.getElementById( "insiCheckOid").value;
    var name  = document.getElementById( "insiCheckBirthName").value;
    var given = document.getElementById( "insiCheckGiven" ).value;
    var ssex  = document.getElementById( "insiCheckSex").value;
    var sex   = parseInt( ssex );
    var date  = document.getElementById( "insiCheckBirthDate" ).value;
    var place = document.getElementById( "insiCheckBirthPlace" ).value;

    dmpConnectInstance.hl_checkInsIdentity( LpsInfos, ins, key, oid, name, given, sex, date, place, function( e )
    {
        if( e.error )
        {
            appendLog( "-- hl_checkInsIdentity failed." , e );
        }
        else 
        {
            appendLog( "-- hl_checkInsIdentity succeeded, identity is valid." , e );
        }
    });
}