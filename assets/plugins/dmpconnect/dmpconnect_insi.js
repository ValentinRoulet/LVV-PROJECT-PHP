DMPConnect.InsIdentityResult =
{
    InsIdentityResult_NoIdentityFound         : 1,
    InsIdentityResult_SingleIdentityFound     : 2,
    InsIdentityResult_MultipleIdentitiesFound : 3
};


/**
 * @brief INSi WS #1 transaction: Get the INS from Vitale card data.
 * 
 * The transaction is performed on the i-th patient read in vitale card.
 * The Vitale card must have been read before calling this function.
 * 
 * @param {int} aVitaleCardIndex        Index of the patient to use as input of the function.
 *  
 *  OUT
 *   {
 *       i_insIdentityResult :         // See enum InsIdentityResult above.
 *   
 *       if i_insIdentityResult is (2) InsIdentityResult_SingleIdentityFound, the following structure is also returned:
 *   
 *       Identity
 *       {
 *         Ins :
 *         {
 *           s_value
 *           s_key
 *           s_oid
 *           s_startDate         // Format YYYY-MM-DD
 *           s_endDate           // Format YYYY-MM-DD
 *         }
 *         s_birthName
 *         s_given
 *         s_birthGiven
 *         i_sex
 *         s_birthDate           // Format YYYY-MM-DD
 *         s_birthPlace          // Format COG ('Code Officiel Géographique' : https://www.insee.fr/fr/information/2560452)
 *         History :                 (*) Only present if there is an history
 *         [
 *           {
 *             s_value
 *             s_key
 *             s_oid
 *             s_startDate       // Format YYYY-MM-DD
 *             s_endDate         // Format YYYY-MM-DD
 *           },
 *           ...
 *         ]
 *       }
 *   }
 */
DMPConnect.prototype.hl_getInsFromVitaleCard = function( lpsParams, aVitaleCardIndex, resultCallBack )
{



    var command = 
    {
        LpsInfos               : lpsParams,
        "i_vitalePatientIndex" : aVitaleCardIndex,

        "s_commandName"        : "hl_getInsFromVitaleCard" ,
        "s_sessionId"          : this.getSessionId()
    };

    return this.sendCommand( command, 60, resultCallBack );
}

/**
  * @brief INSi WS #2 transaction: Get the INS from manually defined patient data.
  * 
  * Input requirements:
  * 
  * The input strings of this transaction must:
  * - only contains capitals letters ;
  * - not contain symbols (no accent, no cedilla) ;
  * - only contains hyphen ('-') and space (' ') as special characters, and hyphen and space cannot be doubled.
  * 
  * The sex can not be "unknown".
  * 
  * The expected date format is YYYY-MM-DD.
  * 
  * The birth place format is COG (Code Officiel Géographique, @see https://www.insee.fr/fr/information/2560452 )
  * 
  * @param {string} name        (Birth) name of the patient
  * @param {string} given       Given of the patient 
  * @param {int}    sex         Sex of the patient (Values in DMPConnect.Sex enum)
  * @param {string} date        Birth date (format YYYY-MM-DD)
  * @param {string} place       (Optional) Birth place 
  * 
  *  OUT
  *   {
  *       i_insIdentityResult :         // enum InsIdentityResult
  *   
  *       if i_insIdentityResult is (2) InsIdentityResult_SingleIdentityFound, the following structure is also returned:
  *   
  *       Identity
  *       {
  *         Ins :
  *         {
  *           s_value
  *           s_key
  *           s_oid
  *           s_startDate         // Format YYYY-MM-DD
  *           s_endDate           // Format YYYY-MM-DD
  *         }
  *         s_birthName
  *         s_given
  *         s_birthGiven
  *         i_sex
  *         s_birthDate           // Format YYYY-MM-DD
  *         s_birthPlace          // Format COG ('Code Officiel Géographique' : https://www.insee.fr/fr/information/2560452)
  *         History :                 (*) Only present if there is an history
  *         [
  *           {
  *             s_value
  *             s_key
  *             s_oid
  *             s_startDate       // Format YYYY-MM-DD
  *             s_endDate         // Format YYYY-MM-DD
  *           },
  *           ...
  *         ]
  *       }
  *   }
  */
 DMPConnect.prototype.hl_getInsFromIdentityInformation = function( lpsParams, name, given, sex, date, place, resultCallBack )
 {
    var command = 
    {
        LpsInfos               : lpsParams,

        "s_birthName"          : name ,
        "s_given"              : given ,
        "i_sex"                : sex ,
        "s_birthDate"          : date,
        "s_birthPlace"         : place,

        "s_commandName"        : "hl_getInsFromIdentityInformation" ,
        "s_sessionId"          : this.getSessionId()
    };

    return this.sendCommand( command, 60, resultCallBack );
 }



 /**
   * @brief INSi WS #3 transaction: Check the INS validity wrt the patient identity informations.
   *
   * IN 
   * {
   *    LpsInfos :              // Software-related informations (authorization numbers, etc.)
   *     {
   *        s_idam    [59],      // Software authorization number. From the CNDA.
   *        s_numAM   [ 2],      // IDAM type. Currently must be set to "04" (Authorization number).
   *        s_version [59],      // Software version. No specific format. E.g. "01.00", or "1.0", or "2".
   *        s_instance[59],      // Software instance UUID in its canonical form. See ISO/IEC 9834-8 et RFC 4122.
   *                             // Must be set, unique and constant per software instance/installation.
   *                             // E.g. "110e8400-e29b-11d4-a716-446655440000"
   *        s_name    [59],      // The software name.
   *        s_billingNumber[33]  // Billing situation. Either FINESS for institutions or AM number in all other cases. Mandatory for INSi.
   *     },
   *         s_ins[13],
   *         s_key[2],
   *         s_oid[32],
   *         s_birthName[80],
   *         s_given[61],
   *         i_sex,
   *         s_birthDate[10],          // Format YYYY-MM-DD
   *     (*) s_birthPlace[5],          // Format COG ('Code Officiel Géographique' : https://www.insee.fr/fr/information/2560452)
   *  } 
   * OUT 
   * {
   *     "Ok" if validation succeeded or ERROR if validation failed (check error detail for more infos)
   * }
   */
 DMPConnect.prototype.hl_checkInsIdentity = function( lpsParams, ins, key, oid, name, given, sex, date, place, resultCallBack )
 {
     var command =
     {
        LpsInfos        : lpsParams,

        "s_ins"         : ins,
        "s_key"         : key,
        "s_oid"         : oid,
        "s_birthName"   : name,
        "s_given"       : given,
        "i_sex"         : sex, 
        "s_birthDate"   : date,
        "s_birthPlace"  : place,
     
        "s_commandName" : "hl_checkInsIdentity",
        "s_sessionId"   : this.getSessionId()    
    };

    return this.sendCommand( command, 60, resultCallBack );
 }

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
 *
 * Returns :
 * - s_instanceUniqueId the instance unique ID of the software. The number is unique per user account and do not change
 *                      between restarts or software updates. Not used here for TLSi transactions.
 * - i_connectionHandle the handle of the connection used for TLSi transactions
 *
 * { [...]
 *    "s_instanceUniqueId" : "2.25.186812548863990774547154938929646584355"
 *    "i_connectionHandle" : 262145
 * }
 *
 * @param {string}   pinCode                      E.g. "1234" for all test cards by default.
 * @param {string}   serverFullUrl                TLSi service URL.
 *                                                Set it to an empty string to use the production DMP.
 * @param {number}   transactionsTimeoutInSeconds Transactions timeout.
 * @param {string}   practiceSetting              E.g. "AMBULATOIRE". See spec.
 * @param {number}   cpsPracticeLocationIndice    Practice location to use. Indice in the CPx card list of practices locations.
 * @param {function} resultCallback               The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_createInsiConnector = function( pinCode, serverFullUrl, transactionsTimeoutInSeconds, practiceSetting, cpsPracticeLocationIndice, optionalParameters, resultCallback )
{
    var serverUrl = "";

    // Empty string => qualification server.
    if( serverFullUrl === "" )
        serverUrl = "https://qualiflps.services-ps.ameli.fr";
    else
        serverUrl = serverFullUrl;

    var command = {
        "s_commandName"               : "hl_createDmpConnector",
        "s_pinCode"                   : pinCode,
        "s_dmpUrl"                    : serverUrl,
        "i_transactionsTimeout"       : transactionsTimeoutInSeconds,
        "s_practiceSetting"           : practiceSetting,
        "i_cpsPracticeLocationIndice" : cpsPracticeLocationIndice,
        "i_getConnectionHandle"       : 1,
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

    // Get connection handle 
    return this.sendCommand( command, 20, resultCallback );
};