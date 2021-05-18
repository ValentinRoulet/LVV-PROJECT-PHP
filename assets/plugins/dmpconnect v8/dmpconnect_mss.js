/**
  * @brief Search a PS through the MSS LDAP Directory.
  * 
  * @param {string} aSearchName         Name of the PS to search. 
  * @param {string} aSearchGiven        Given of the PS to search.
  * @param {string} aSearchRPPS         RPPS number of the PS to search.
  * @param {string} aSearchSpecialty    Specialty of the PS to search.
  * @param {string} aSearchOrganization Organization of the PS to search.
  * 
  * @note Any search criteria can contain wildcard (ex: "mar*" will match "marie", "marc", "marion", ...).
  * @note If search criteria is empty, it will be replaced with a global wildcard "*".
  *
  * 
  * IN
  * {
  *     (*) s_searchName        :           // Name of the PS to search.
  *     (*) s_searchGiven       :           // Given of the PS to search.
  *     (*) s_searchRPPS        :           // RPPS number (IdNat) of the PS to search.
  *     (*) s_searchSpecialty   :           // Specialty of the PS to search.
  *     (*) s_searchOrganization:           // Organization of the PS to search.
  * } 
  * OUT 
  * {
  *     s_xmlResultInBase64 :               // Raw request result in Base64.
  *     PSList :
  *     [
  *         {
  *             s_commonName :                  // "Name" + "Given" + "-" + "Code Profession" 
  *             s_name:                         // Name
  *             s_given:                        // Given 
  *             s_postalAddress:                // Postal address 
  *             s_postalCode:                   // Zip Code
  *             s_city:                         // City 
  *             s_departement:                  // Department
  *             s_country:                      // Country
  *             s_mail:                         // MSS Email
  *             s_civiliteExercice:             // Civility
  *             s_title:                        // "Code Profession" + "Specialite Ordinale"
  *             s_idNat:                        // RPPS or Adeli
  *             s_codeProfession:               // Libellé court Profession
  *             s_codeCategorieProfession:      // Code catégorie profession
  *             s_libelleCategorieProfession:   // Libellé long Profession
  *             s_specialiteOrdinale:           // Code Spécialité
  *             s_organisation:                 // Raison sociale (si non vide) - Enseigne commerciale (si raison sociale vide)
  *             s_raisonSociale:                // Raison sociale 
  *             s_company:                      // Raison sociale (si non vide) - Enseigne commerciale (si raison sociale vide)
  *             s_enseigneCommerciale:          // Enseigne commerciale.
  *         },
  *         {
  *             ...
  *         }
  *     ]
  * }
  * 
  * 
  * (*) Optional parameters, at least one of the five should be set.  
  */
 DMPConnect.prototype.hl_getMssHpInfos = function (aSearchName, aSearchGiven, aSearchRPPS, aSearchSpecialty, aSearchOrganization, resultCallback) 
 {
     var command =
     {
         "s_commandName"         : "hl_getMssHpInfos",
         "s_sessionId"           : this.getSessionId(),
         "s_searchName"          : aSearchName,
         "s_searchGiven"         : aSearchGiven,
         "s_searchRPPS"          : aSearchRPPS,
         "s_searchSpecialty"     : aSearchSpecialty,
         "s_searchOrganization"  : aSearchOrganization
     };
     return this.sendCommand(command, 30, resultCallback);
 }
 /**
  * @brief Call send MSS request
  *
  * @param {string} requestPath          Path of the MSS request (without server name ex:  "/mss-msg-services/services/Folder/soap/v1/listFolders" )
  * @param {string} requestBodyInBase64  MSS request encoded in base64.
  *
  * 
  * IN 
  * {
  *      s_requestPath         :                 // Path URL part of the request. Ex: "/mss-msg-services/services/Folder/soap/v1/listFolders".
  *      s_requestBodyInBase64 :                 // Body of the request in Base64.
  * }
  * OUT 
  * {
  *      s_answerBodyInBase64  :                 // MSS answer in Base64.
  *      Headers : [ s_name : "" : s_value ]     // HTTP headers of the answer.
  *      StatusLine :                            // Standardized status line (RFC 2616).
  *      {
  *          s_httpVersion  : "" ,
  *          s_reasonPhrase : "" ,
  *          s_statusCode   : "" 
  *      }
  * }
  */
 DMPConnect.prototype.hl_sendMssRequest = function (requestPath, requestBodyInBase64, resultCallback)
 {
     var command = 
     {
         "s_commandName"         : "hl_sendMssRequest",
         "s_sessionId"           : this.getSessionId(),
         "s_requestPath"         : requestPath,
         "s_requestBodyInBase64" : requestBodyInBase64 
     };
     return this.sendCommand(command, 30, resultCallback);
 }
 /**
   * @brief Send an Email through MSS.
   * 
   * @param {string} requestPath         Path of the WS URL (ie: without server name, ex: "/mss-msg-services/services/Item/soap/v1/sendMessage" )
   * @param {string} sender              Email of the sender.
   * @param {string} recipient           Email(s) of the recipient.
   * @param {string} cc                  Email(s) in Copy Carbon. Can be empty. 
   * @param {string} bcc                 Email(s) in BCC. Can be empty.
   * @param {string} title               Title of the email.
   * @param {string} contentInBase64     Message Content in Base64 format. 
   * @param {int} contentHtml            If 1, content is considered as HTML, if 0 content is considered as RAW text.
   * @param {string} attachmentList      Array of attachments (see below for format). Can be an empty array.
   *
   * IN
   * {
   *      s_requestPath            : ""                          // ex: "/mss-msg-services/services/Item/soap/v1/sendMessage"
   *      s_senderEmail            : ""
   *      s_recipient              : ""                          // Use ';' to separate multiple emails. ex: "toto@mss.fr;tata@mss.fr"
   *      (*) s_cc                 : ""                          // Use ';' to separate multiple emails. ex: "toto@mss.fr;tata@mss.fr"
   *      (*) s_bcc                : ""                          // Use ';' to separate multiple emails. ex: "toto@mss.fr;tata@mss.fr"
   *      s_title                  : ""
   *      s_messageContentInBase64 : ""
   *      (*) i_messageContentHtml      
   *      (**) Attachments
   * }
   * OUT
   * {
   * 
   * }
   * 
   *  (*) Optional parameter.
   *  (**) Optional parameter. If used, Attachments is an array of attachment structure defined as follow:
   *  Attachments:
   * [
   *       {
   *           s_patientIns            :                           // INS number of the related patient.
   *           s_fileContentInBase64   :                           // Content of the attachment in Base64 format.
   *           s_documentTitle         :                           // Title of the attachment.
   *           s_documentDescription   :                           // Description of the attachment.
   *           s_documentCategory      :                           // Category of the attachment. Ex: 11488-4.
   *           i_documentFormat        :                           // Format of the attachment. Value in DocumentFormat enum.
   *           s_healthcareSetting     :                           // Healthcare setting when the attachment was created. Ex SA07.
   *      },
   *      {
   *          s_patientIns: 
   *          ...
   *      }
   * ]
   */
 DMPConnect.prototype.hl_sendMssEmail = function( requestPath, sender, recipient, cc, bcc, title, contentInBase64, contentHtml, attachmentList, resultCallback )
 {
     var command =
     {
         "s_commandName"            : "hl_sendMssEmail",
         "s_sessionId"              : this.getSessionId(),
         "s_requestPath"            : requestPath,
         "s_senderEmail"            : sender,
         "s_recipient"              : recipient,
         "s_cc"                     : cc,
         "s_bcc"                    : bcc,
         "s_title"                  : title,
         "s_messageContentInBase64" : contentInBase64,
         "i_messageContentIsInHtml" : contentHtml
     };
     if( attachmentList.length > 0 )
     {
         command.Attachments = attachmentList;
     }
     return this.sendCommand( command, 30, resultCallback );
 }
 /**
   * @brief Function used to generate a buffer containing attachments usable in sendMessage request.
   * 
   * @param{string} attachmentList        Array of attachment used to generate the standardized attachment buffer. See bellow for description of the format of the array.
   * 
   * 
   * IN
   * {
   *  Attachments:
   *  [
   *       {
   *           s_patientIns            :                           // INS number of the related patient.
   *           s_fileContentInBase64   :                           // Content of the attachment in Base64 format.
   *           s_documentTitle         :                           // Title of the attachment.
   *           s_documentDescription   :                           // Description of the attachment.
   *           s_documentCategory      :                           // Category of the attachment. Ex: 11488-4.
   *           i_documentFormat        :                           // Format of the attachment. Value in DocumentFormat enum.
   *           s_healthcareSetting     :                           // Healthcare setting when the attachment was created. Ex SA07.
   *      },
   *      {
   *          s_patientIns: 
   *          ...
   *      }
   *  ]
   * }
   */
 DMPConnect.prototype.hl_generateMssAttachments = function (attachmentList, resultCallback) 
 {
     var command =
     {
         "s_commandName": "hl_generateMssAttachments",
         "s_sessionId": this.getSessionId(),
         "Attachments": attachmentList
     };
     return this.sendCommand(command, 30, resultCallback);
 }