/**
 * DmpConnect-JS JavaScript wrapper.
 * Copyright (c) 2016, icanopee SAS. All right reserved.
 *
 * Version 0.9.9
 */

/** Return a string holding the object properties in HTML.
 *
 * @param {object} o     The object to dump.
 * @param {string} shift Offset to apply to the display.
 */
function printObject( o, shift )
{
    shift = shift || "";
    var defaultSpacing = "  ";

    var out = shift + '{\n';
    for ( var p in o )
    {
        var value = o[p];

        if( o[p] !== null && typeof o[p] === 'object' )
            // Recursive call to get the object properties
            value = printObject( o[p], shift + defaultSpacing );

        out += defaultSpacing + shift + '"' + p + '" : ' + value + '\n';
    }
    out += shift + "}\n";
    return out
}


const INITIAL_STATE = {
    session: false,
    PcscReaders: false,
    cpxCard: false,
    cpxCardRead: false,
    vitaleCard: false,
    vitaleCardRead: false,
    DmpConnector: false,
};
const INITIAL_DMP_STATE = {
    certifiedIdentity: false,
    directAuthenticationStatus: false,
};

/**
 * DMPConnect class.
 *
 * Parameter names conventions
 *    Prefix "s_": string.
 *    Prefix "i_": integer.
 *    Prefix "f_": float.
 *
 * Common input command parameters:
 *   "s_name"      : Command identifier. Mandatory.
 *   "s_sessionId" : Input parameter that is mandatory for all commands except hl_openSession(). Returned by hl_openSession().
 *
 * Output parameters:
 *   "s_status" : Always returned. Can be "OK", "ERROR" or "API_ERROR".
 *
 *   If s_status == "ERROR" or s_status == "API_ERROR"
 *     "s_errorDescription" : A description of the error.
 *
 *   If status == "API_ERROR"
 *     "i_apiErrorType"                 : See ErrorType in dmp_api_errors.h.
 *     "s_apiErrorTypeDescription"      : It is the a textual description of the error type.
 *     "i_apiErrorCode"                 : See ApiErrorCode in dmp_api_errors.h.
 *     "s_apiErrorDescription"          : Description of the error.
 *     "s_apiErrorContext"              : DmpConnect function that raised the error.
 *     "s_apiErrorExtendedInformations" : More informations about the problem.
 *
 *   If s_status == "OK" and the command wraps an API function:
 *     "i_returnValue" : The return value of the original function.
 */

/**
 * Constructor.
 *
 * @param    {function}     logCallbackFunc     Log function to use during methods execution.
 * @param    {function}     refreshCallbackFunc Function called when the internal object state has been updated.
 *                                               i.e. for example, when the session id has been set after handling hl_openSession
 *                                               or hl_closeSession.
 * @param    {function}     errorHandler        Function called when an error is raised.
 * @param    {string}       localServerPort     Optional. Websockets server port number. Default: "9982".
 * @param    {string}       localServerDomain   Websockets server domain name. Eg. "localhost.icanopee.net".
 */
function DMPConnect( logCallbackFunc, refreshCallbackFunc, errorHandler, initCallback, localServerPort, localServerDomain )
{
    // Default value.
    localServerPort = localServerPort || 9982;

    this._port            = localServerPort;
    this._sessionId       = "";
    this._logCallback     = logCallbackFunc;
    this._refreshCallback = refreshCallbackFunc;
    this._errorHandler    = errorHandler;
    this._serverDomain    = localServerDomain;
    this._serverPort      = localServerPort;

    if( localServerDomain )
    {
        this._serverUrl = "wss://" + localServerDomain + ":" + localServerPort;
        if( initCallback )
            initCallback( this._serverUrl );
    }

    this._state = { ...INITIAL_STATE };
    this._dmpState = { ...INITIAL_DMP_STATE };
};

DMPConnect.prototype.getState = function() {
  return { ...this._state, dmp: { ...this._dmpState } };
};
DMPConnect.prototype.setState = function(key, value) {
  this._state[key] = value;
};
DMPConnect.prototype.setDmpState = function(key, value) {
  this._dmpState[key] = value;
};
DMPConnect.prototype.resetState = function() {
  this._dmpState = { ...INITIAL_DMP_STATE };
  this._state = { ...INITIAL_STATE };
};


DMPConnect.prototype.getSessionId = function()
{
    return this._sessionId;
};

/**
 * Send a command to DmpConnect-JS server.
 * If the answer is not received within the given time frame (@see timeoutInSeconds), or if there is a network communication error,
 *  the error handler is called.
 *
 * If the answer is received and is correctly formatted, the answer callback is called.

 * The callback is given one parameter: an object holding the server answer in its properties.
 *
 * About the commands:
 *   DmpConnect-JS expects JSON-formatted input commands.
 *   The command is built (JSON.stringify) from the properties of the 'command' object given in parameter.
 *   The server answer is also JSON-formatted. It is deserialized into an object (with JSON.parse) that is given to the 'success callback'
 *    that should process the answer.
 *
 * @param {object}   command          Will be serialized to a JSON string to create the command.
 *
 * @param {int}      timeoutInSeconds Timeout for the server answer.
 *
 * @param {function} answerCallback   The callback that will receive the response from the local server.
 *                                    When the command is sent with a persistent socket (see next parameter), this
 *                                     function is expected to return true or false.
 *                                    If false is returned, the socket is closed. Otherwise the socket remains open.
 *
 * @param {boolean}  persistentSocket Set to false by default.
 *                                    If set to true, answerCallback will receive asynchronous messages from the local server and
 *                                     the socket will not be closed after having received the answer. See answerCallback.
 *                                    If set to false, the command is considered synchronous. In this case, the following operations are performed:
 *                                     (1) the command is sent, (2) the answer is received, (3) the socket is closed.
 * @param {function} wrapperCallback  DMPConnect inner function for object related handling on command response
 *
 * @return {object} The websocket object.
 */
DMPConnect.prototype.sendCommand = function(command, timeoutInSeconds, answerCallback, persistentSocket, wrapperCallback )
{
    // The case below occur if getDomain() failed or have not yet retreived the domain name, or if an empty string has been given to the contructor.
    if( ! this._serverUrl )
    {
        var error = this.createWrapperError( DMPConnect.WrapperErrorCode.InitializationError, "DmpConnect-JS server address is not defined." );
        return this._errorHandler( error, command, timeoutInSeconds, answerCallback);
    }

    if( ! window.hasOwnProperty("WebSocket") )
    {
        // The browser doesn't support WebSocket
        var error = this.createWrapperError(DMPConnect.WrapperErrorCode.BrowserNotSupported, "Websocket is not supported by your browser");
        return this._errorHandler( error, command, timeoutInSeconds, answerCallback);
    }

    persistentSocket = persistentSocket || false;

    this._logCallback( "Opening a connection on [" + this._serverUrl + "].");

    var ws = new WebSocket( this._serverUrl )

    var timer = null;

    // Socket opened callback.
    ws.onopen = (function(self, ws, timeoutInSeconds, answerCallback, persistentSocket, wrapperCallback)
    {
        return function ()
        {
            var message = JSON.stringify(command);

            try
            {
                var forceCloseSocket = (function(ws, self, command, timeoutInSeconds, answerCallback, wrapperCallback)
                {
                    return function ()
                    {
                        // Close the connection.
                        ws.close();
                        var error = self.createWrapperError(DMPConnect.WrapperErrorCode.ServerTimeout, 'Server timeout reached.');
                        if (wrapperCallback)
                            wrapperCallback(error);

                        return self._errorHandler( error, command, timeoutInSeconds, answerCallback);
                    }
                })(ws, self, command, timeoutInSeconds, answerCallback, wrapperCallback);

                ws.send( message );
                self._logCallback( "Message sent [" + message + "].", command );
                // Set a timer for the server answer timeout.
                if (!persistentSocket)
                    timer = setTimeout(forceCloseSocket, timeoutInSeconds * 1000);
            }
            catch( exception )
            {
                // Close the connection.
                ws.close();
                var error = self.createWrapperError(DMPConnect.WrapperErrorCode.SocketError, 'Websocket has thrown an unhandled exception.');
                if (wrapperCallback) wrapperCallback(error);
                return self._errorHandler( error);
            }
        }
    })( this, ws, timeoutInSeconds, answerCallback, persistentSocket, wrapperCallback );

    // Change the message handler since we expect an answer from the server.
    if( persistentSocket )
    {
        // Here, we just handled a command that is expected to return multiple answers from time to time,
        //  so we don't close the websocket and change its message handling callback to the following.
        ws.onmessage = (function(self, command, timeoutInSeconds, answerCallback, persistentSocket, wrapperCallback)
        {
            return function (event)
            {
                var receivedData = JSON.parse( event.data );

                self._logCallback( "Asynchronous data received\n");
                self._logCallback( "Async data:\n" + event.data + "\n");
                if (wrapperCallback) wrapperCallback(receivedData);

                // Directly end the dialog if an error occurred while trying to start the monitoring session.
                if( receivedData.s_status != "OK" )
                {
                    // Close the connection.
                    ws.close();
                    return self._errorHandler( receivedData, command, timeoutInSeconds, answerCallback, persistentSocket );
                }

                // Call the async callback.
                // The callback is expected to return true or false.
                // - false will close the socket and stop the server from sending notifications.
                // - true indicate that the user still need notifications.
                answerCallback( receivedData, (function (ws, receivedData, self, answerCallback )
                {
                    return function( keepSending )
                    {
                        if( keepSending )
                        {
                            self._logCallback( "-- Async data handled.");
                        }
                        else
                        {
                            self._logCallback( "-- Async data handled. Closing the websocket.");
                            ws.close();

                            // Call the user answer callback.
                            answerCallback( receivedData );
                        }
                    }
                })( ws, receivedData, self, answerCallback ));
            }
        })(this, command, timeoutInSeconds, answerCallback, persistentSocket, wrapperCallback);
    }
    else
    {
        // Non persistent socket: parse the answer, close the socket and call the error handler (if needed) then the user callback.
        ws.onmessage = (function(self, command, timeoutInSeconds, answerCallback, wrapperCallback)
        {
            return function (event)
            {
                var receivedData = JSON.parse(event.data);
                self._logCallback( "Synchronous answer received.");
                self._logCallback( "Answer:\n" + event.data + "\n");

                // Disable the timeout timer.
                clearTimeout(timer);

                // Close the socket.
                ws.close();

                if( receivedData.s_status != 'OK' )
                {
                    return self._errorHandler( receivedData, command, timeoutInSeconds, answerCallback );
                }

                if (wrapperCallback) wrapperCallback(receivedData);
                answerCallback( receivedData );
            }
        })(this, command, timeoutInSeconds, answerCallback, wrapperCallback);
    }

    ws.onclose = (function(self, command, timeoutInSeconds, answerCallback, wrapperCallback)
    {
        return function (event)
        {
            var code     = event.code;
            var reason   = event.reason;
            var wasClean = event.wasClean;

            if (!reason) {
                // See http://tools.ietf.org/html/rfc6455#section-7.4.1
                     if (code == 1000) reason = "Normal closure, meaning that the purpose for which the connection was established has been fulfilled.";
                else if (code == 1001) reason = "An endpoint is \"going away\", such as a server going down or a browser having navigated away from a page.";
                else if (code == 1002) reason = "An endpoint is terminating the connection due to a protocol error";
                else if (code == 1003) reason = "An endpoint is terminating the connection because it has received a type of data it cannot accept (e.g., an endpoint that understands only text data MAY send this if it receives a binary message).";
                else if (code == 1004) reason = "Reserved. The specific meaning might be defined in the future.";
                else if (code == 1005) reason = "No status code was actually present.";
                else if (code == 1006) reason = "The connection was closed abnormally, e.g., without sending or receiving a Close control frame";
                else if (code == 1007) reason = "An endpoint is terminating the connection because it has received data within a message that was not consistent with the type of the message (e.g., non-UTF-8 [http://tools.ietf.org/html/rfc3629] data within a text message).";
                else if (code == 1008) reason = "An endpoint is terminating the connection because it has received a message that \"violates its policy\". This reason is given either if there is no other sutible reason, or if there is a need to hide specific details about the policy.";
                else if (code == 1009) reason = "An endpoint is terminating the connection because it has received a message that is too big for it to process.";
                // Note that this status code is not used by the server, because it can fail the WebSocket handshake instead.
                else if (code == 1010) reason = "An endpoint (client) is terminating the connection because it has expected the server to negotiate one or more extension, but the server didn't return them in the response message of the WebSocket handshake. <br /> Specifically, the extensions that are needed are: " + event.reason;
                else if (code == 1011) reason = "A server is terminating the connection because it encountered an unexpected condition that prevented it from fulfilling the request.";
                else if (code == 1015) reason = "The connection was closed due to a failure to perform a TLS handshake (e.g., the server certificate can't be verified).";
                else
                    reason = "Unknown reason";
            }

            // Websocket closed with an error?
            if (code != 1000)
            {
                var errorDesc = "Connection closed with an error. Status: " + code + ". Reason: " + reason + ". wasClean: " + wasClean + ".";
                var error     = self.createWrapperError(DMPConnect.WrapperErrorCode.ConnectionClosedError, errorDesc);

                if (wrapperCallback) wrapperCallback(error);
                return self._errorHandler( error, command, timeoutInSeconds, answerCallback);
            } else
            {
                self._logCallback( "Connection closed.");
            }
        }
    })(this, command, timeoutInSeconds, answerCallback, wrapperCallback);

    ws.onerror = (function(self, command, timeoutInSeconds, answerCallback, wrapperCallback)
    {
        return function ()
        {
            var error = self.createWrapperError( DMPConnect.WrapperErrorCode.SocketError, "An error occurred on the socket" );
            if (wrapperCallback) wrapperCallback(error);
            return self._errorHandler( error, command, timeoutInSeconds, answerCallback);
        }
    })( this, command, timeoutInSeconds, answerCallback, wrapperCallback );

    return ws;
};

function getXhr() 
{
    if(window.XMLHttpRequest || window.ActiveXObject)
    {
        if(window.ActiveXObject)
        {
            try
            {
                return new ActiveXObject("Msxml2.XMLHTTP");
            }catch(e)
            {
                return new ActiveXObject("Microsoft.XMLHTTP");
            }
        }
        else
        {
            return new XMLHttpRequest(); 
        }
    }
    else
    {
        alert("Your browser does not support XMLHTTPRequest" );
        return null;
    }
}

DMPConnect.prototype.sendHttpCommand = function( aCommandData, anEntryPoint, aCallBackFunction )
{
    var xhr = getXhr();
    var url = "https://" + this._serverDomain + ":" + this._serverPort + "/remotecommand/" + anEntryPoint + "" ;

    var message = JSON.stringify(aCommandData);
    console.log( "Sending to '" + url + "' the following message '" + message + "'." );

    xhr.onreadystatechange = function() 
    {
        if( xhr.readyState == 4 )  // Complete 
        {
            jsonData = JSON.parse( xhr.responseText );
            console.log( "HTTP command answer : " + JSON.stringify( jsonData ) );
            
            if( aCallBackFunction )
            {
                if( jsonData.s_status != "OK" )
                {
                    aCallBackFunction( { 'error' : jsonData } );
                }
                else 
                {
                    aCallBackFunction( jsonData );
                }
            }
        }
    }

    // Send request.
    xhr.open( "POST" , url , true );
    xhr.send( message );
}

/**
 *  @brief Wrapper and DmpConnect API error types.
 */
DMPConnect.ErrorType =
{
    // DmpConnect API error types.
    ApiErrors:      1,
    DMPErrors:      2,
    CurlErrors:     3,
    VitaleErrors:   4,

    // JS Wrapper errors.
    WrapperErrors:  5000,

    // DmpConnect-JS server software errors.
    DmpConnectJsSoftwareErrors:  6000
};

/**
 *  @brief DMPConnect wrapper related error codes (WrapperErrors)
 */
DMPConnect.WrapperErrorCode =
{
    BrowserNotSupported:    1,
    SocketError:            2,
    ConnectionClosedError:  3,
    UnexpectedResponse:     4,
    ServerTimeout:          5,
    InitializationError:    6
};

/**
 *  @brief Error codes for DmpConnectJsSoftwareErrors error type.
 *         It is software errors returned by the WS server.
 */
DMPConnect.DmpConnectJsErrorCodes =
{
    ErrTechnical     : 1, // All internal software error (allocations, bugs, etc.).
    ErrFunctional    : 2, // Connector not initialized, no reader specified, unknown command, missing parameter, invalid input data (too wide, invalid type, missing, etc.)
                          // That kind of errors must be handled during the application development.
    ErrInvalidSession: 3  // Unknown session.
};

/**
 * Create a wrapper error having the format of errors returned by DmpConnect API.
 * This error object is expected to be handled by the _errorHandler callback.

 * @param {number} errorCode
 * @param {string} errorDesc
 * @param {string} errorTypeDescription
 * @param {string} errorContext
 * @param {string} errorExtendedInformations
 */
DMPConnect.prototype.createWrapperError = function( errorCode, errorDesc, errorTypeDescription, errorContext, errorExtendedInformations )
{
    errorTypeDescription      = errorTypeDescription      || '';
    errorContext              = errorContext              || '';
    errorExtendedInformations = errorExtendedInformations || '';

    return {
        i_apiErrorType:                 DMPConnect.ErrorType.WrapperErrors, // The other kind of error are returned by DmpConnect API functions.
        i_apiErrorCode:                 errorCode,
        s_apiErrorDescription:          errorDesc,
        s_apiErrorTypeDescription:      errorTypeDescription,
        s_apiErrorContext:              errorContext,
        s_apiErrorExtendedInformations: errorExtendedInformations
    };
};

/**
 *   If DmpConnect-JS is released with MSS connect, the MssParameters structure must be set in the optionalParameters parameters:
 *   MssParameters
 *   {
 *      s_numHomologation : "" ,            // A string that identify the software that use MSS. it is used for debugging purpose with ASIP, and must contains : name of the company, name of the software. Total length must not exceed 50 characters.
 *      s_producerAddress : "" ,            // Address (address + phone number) of the structure of the producer of the attachment. Lines must be separated by the newline "\n" character, ex: "10, Grand Rue\n50 170 Mont-Saint-Michel".
 *      s_softwareDetails : "" ,            // Details of the software that is used to generate the MSS messages. Details must includes the name, version and a contact, ex: "BIOLO version 1.1 (de la société BIOLOLOGIC au 01 58 45 00 00)"
 *   }
 *
 *  Optional parameters : 
 *  i_disableUpdateCheck            Disable check of the last version of the product.
 *  s_updateCheckChannel            Update channel used to get last version ("prod", "dev", "beta").
 *

 * Specific JSON output:
 * {
 *   "s_sessionId"      : "<The session id - a GUID>"
 *   "s_serviceVersion" : DmpConnect-JS version. Ex.: "0.9.9".
 * 
 *   // Optional Proxy settings that will override the global settings (xml settings), only for this session. 
 *   "s_proxyIpOrUrl"   : Proxy server or IP. 
 *   "i_proxyPort"      : Proxy port.
 *   "s_proxyLogin"     : Proxy login.
 *   "s_proxyPassword"  : Proxy password.
 *
 *   "s_applicationId"  : Identifier of the target application. Each application Id exists in a single process.
 *
 *   The following informations are returned mostly for information purposes.
 *  
 *
 *   If a proxy is defined (either in the global configuration (dmpconnect-js2.xml) or in the session):
 *     "s_proxyServer"
 *     "i_proxyPort"
 *     "s_proxyLogin"
 *     "s_proxyPass"
 *
 *  If a ntp server is defined:
 *    "s_ntpServer"
 *    "i_ntpPort"
 *
 *  If i_disableUpdateCheck is not 1:
 *    "s_lastVersionNumber"         Last version number of the product.
 *    "s_lastVersionUpdateLog"      Last version update log.
 *    "s_lastVersionLinkStr"        Last version download link.
 * }
 *
 * @param {number}   timeoutInSeconds   If no operation on the session occurred within the given delay (in seconds), it is automatically closed.
 *                                      Default value: 1 hour.
 * @param {object}  optionalParameters  Used to pass additional parameters (proxy).
 * @param {function} resultCallback     The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_openSession = function(timeoutInSeconds, optionalParameters, resultCallback)
{
    timeoutInSeconds = timeoutInSeconds || 3600;

    var command = {
        "s_commandName"      : "hl_openSession",
        "i_timeoutInSeconds" : timeoutInSeconds
    };

    if( dmpConnectJSUseMss ){   command = Object.assign( MssParameters , command ); }
    var tseUserName = document.getElementById(  "TseUserName" );     var tseDomainName = document.getElementById( "TseDomainName" );     if( tseUserName && tseDomainName )     {        if( tseUserName.value.length > 0 && tseDomainName.value.length > 0 )        {            command.s_userName   = tseUserName.value;             command.s_domainName = tseDomainName.value;  
        }     }     
    

    // Proxy 
    if( optionalParameters !== undefined )
    {        
        console.log( optionalParameters );

        if( optionalParameters.s_proxyIpOrUrl !== undefined &&
            optionalParameters.i_proxyPort !== undefined && 
            optionalParameters.s_proxyLogin !== undefined && 
            optionalParameters.s_proxyPassword !== undefined )
        {
            console.log( optionalParameters );

            command.s_proxyIpOrUrl   = optionalParameters.s_proxyIpOrUrl;
            command.i_proxyPort      = optionalParameters.i_proxyPort;
            command.s_proxyLogin     = optionalParameters.s_proxyLogin;
            command.s_proxyPassword  = optionalParameters.s_proxyPassword;
        }

        if( optionalParameters.s_applicationId !== undefined )
        {
            command.s_applicationId = optionalParameters.s_applicationId
        }
    }


    return this.sendCommand(command, 30, resultCallback, false, (function(self){
        return function(result) {
            if (result.s_status == 'OK') {
                self._sessionId = result.s_sessionId;
                self.setState('session', true);
                self._refreshCallback();
            }
        }
    })(this));
};

/**
  * @brief For debugging purpose only.
  * Force a segfault inside the server.
  */
DMPConnect.prototype.hl_crash = function()
{
    var command = 
    {
        "s_commandName" : "hl_crash" 
    };

    return this.sendCommand( command, 30 , undefined, false, (function (self)
    {
        return function( result ) 
        {
            self._refreshCallback();
        }
    })( this ));
}

/**
  * @brief For debugging purpose only.
  * Force a child process to be a zombie.
  */
DMPConnect.prototype.hl_zombie = function()
{
    var command = 
    {
        "s_commandName" : "hl_zombie",
        "s_sessionId"   : this.getSessionId()
    }

    return this.sendCommand( command, 2, undefined );
}

/**
  * @brief Same as hl_openSession but use a custom dcparameter
  */
DMPConnect.prototype.hl_openSessionWithCustomDcParam = function(aDcParameter,timeoutInSeconds, optionalParameters, resultCallback)
{
    timeoutInSeconds = timeoutInSeconds || 3600;

    var command = {
        "s_commandName"      : "hl_openSession",
        "i_timeoutInSeconds" : timeoutInSeconds,
        "s_dcparameters64"   : aDcParameter
    };

    if( dmpConnectJSUseMss ){   command = Object.assign( MssParameters , command ); }
    var tseUserName = document.getElementById(  "TseUserName" );     var tseDomainName = document.getElementById( "TseDomainName" );     if( tseUserName && tseDomainName )     {        if( tseUserName.value.length > 0 && tseDomainName.value.length > 0 )        {            command.s_userName   = tseUserName.value;             command.s_domainName = tseDomainName.value;  
        }     }     

    // Proxy 
    if( optionalParameters !== undefined )
    {        
        if( optionalParameters.s_proxyIpOrUrl &&
            optionalParameters.i_proxyPort && 
            optionalParameters.s_proxyLogin && 
            optionalParameters.s_proxyPassword )
        {
            console.log( optionalParameters );

            command.s_proxyIpOrUrl   = optionalParameters.s_proxyIpOrUrl;
            command.i_proxyPort      = optionalParameters.i_proxyPort;
            command.s_proxyLogin     = optionalParameters.s_proxyLogin;
            command.s_proxyPassword  = optionalParameters.s_proxyPassword;
        }
    }

    return this.sendCommand(command, 30, resultCallback, false, (function(self){
        return function(result) {
            if (result.s_status == 'OK') {
                self._sessionId = result.s_sessionId;
                self.setState('session', true);
                self._refreshCallback();
            }
        }
    })(this) );
}

/**
 * This function will issue a message every seconds until the socket or the session is closed.
 * For development/tuning.
 *
 * IN
 * OUT
 *  "sMessage" : "Test" : Always return this.
 */
DMPConnect.prototype.hl_startDummyMonitoring = function(resultCallback)
{
    var command = {
        "s_commandName" : "hl_startDummyMonitoring",
        "s_sessionId"   : this.getSessionId()
    };

    return this.sendCommand( command, 2, resultCallback, true );
};

/**
 * This functions initiate the monitoring of the "current CPx card", that should have been set first
 *  with a call to hl_getCpxCard().
 * After the standard aknowledgement answer, a first asynchronous event is always sent: it holds the current status
 *  of the CPx card.
 * After this, the smartcard reader is checked every "i_checkingInterval" seconds for a status change.
 * If the status did not change, nothing is sent.
 *
 * To stop monitoring, simply close the websocket.
 *
 * The status is returned as an integer (i_cardStatus) and also as a string (s_cardStatus).
 *
 * IN
 *   "i_checkingInterval" : CPx card checking interval in seconds.
 * OUT
 *   "i_cardStatus" : 0..7
 *   "s_cardStatus" : Textual description of the status. See below.
 *
 *  i_cardStatus: s_cardStatus
 *     0: NotInitialized      Should not happen.
 *     1: ValidCardInserted   A valid (not expired) CPx card is inserted.
 *     2: NoCardInserted      The smartcard reader did not found a card.
 *     3: UnknownCardInserted The card in the smartcard reader is not a CPx.
 *     4: CardStatusChanged   Card status has changed since the last access: the card has been removed, or has been changed. Please recreate a new CPx object.
 *     5: CardIsBlocked       Too many pin code errors.
 *     6: CardIsExpired       The validity end date of the card is above the current date.
 *     7: CpxReaderError      All possible errors: galss, missing reader, invalid setup, etc.
 * Other: InvalidStatus       Should not happen.
 */
DMPConnect.prototype.hl_startCpxCardMonitoring = function(checkingIntervalInSeconds, resultCallback)
{
    var command = {
        "s_commandName"      : "hl_startCpxCardMonitoring",
        "i_checkingInterval" : checkingIntervalInSeconds,
        "s_sessionId"        : this.getSessionId()
    };

    return this.sendCommand(command, 15, resultCallback, true);
};

/**
 * This functions initiate the monitoring of the "current Vitale card", that should have been set first
 *  with a call to hl_getVitaleCard().
 * After the standard aknowledgement answer, a first asynchronous event is always sent: it holds the current status
 *  of the Vitale card.
 * After this, the smartcard reader is checked every "i_checkingInterval" seconds for a status change.
 * If the status did not change, nothing is sent.
 *
 * To stop monitoring, simply close the websocket.
 *
 * The status is returned as an integer (i_cardStatus) and also as a string (s_cardStatus).
 *
 * IN
 *   "i_checkingInterval" : CPx card checking interval in seconds.
 * OUT
 *   "i_cardStatus" : See below.
 *   "s_cardStatus" : Textual description of the status. See below.
 *   If a Vitale card is inserted:
 *       "i_isDemoCard" : 0: real Vitale card. 1: demo or test card.
 *       "VitaleContent" : see hl_readVitaleCard.
 *
 *  i_cardStatus & s_cardStatus
 *     0: NotInitialized      Should not happen.
 *     1: ReadError           A read error occurred: faulty , invalid, saturated, unknown, silent, read error...
 *     2: NewCardFound        The Vitale card has changed since the last check.
 *    (3: CardFound)          Never returned. A valid (not expired) Vitale card is inserted.
 *     4: CardMissing         Not card inserted in the smartcard reader.
 * Other: InvalidStatus       Should not happen.
 */
DMPConnect.prototype.hl_startVitaleCardMonitoring = function(checkingIntervalInSeconds, resultCallback)
{
    var command = {
        "s_commandName"      : "hl_startVitaleCardMonitoring",
        "i_checkingInterval" : checkingIntervalInSeconds,
        "s_sessionId"        : this.getSessionId()
    };

    return this.sendCommand( command, 15, resultCallback, true );
};




/**
  * @brief This function initiate the Virtual printer monitoring.
  *
  * To stop monitoring, simply close the websocket.
  *
  *
  * After each add of a document, the set of all pending documents is returned as an array of VirtualPrinterDocuments.
  *
  * OUT
  * {
  *     VirtualPrinterDocuments :
  *     [
  *         {
  *              "s_id" : "" ,
  *              "i_fileSize" : "",
  *              "s_fileName" : "",
  *              "s_contentInBase64" : ""
  *         }
  *     ]
  * }
  *
  * @note if no document is available, the structure is empty.
  */
DMPConnect.prototype.hl_startPrintedDocumentsMonitoring = function( resultCallback )
{
    var command =
    {
        "s_commandName" : "hl_startPrintedDocumentsMonitoring" ,
        "s_sessionId"   : this.getSessionId()
    };

    return this.sendCommand( command, 15 , resultCallback, true );
}

/**
  * @brief Remove all pending documents.
  *
  * @param documentsIds     (Optional) A set of document ids to remove.
  */
DMPConnect.prototype.hl_flushPrintedDocuments = function (documentsIds, resultCallback)
{
    var command =
    {
        "s_commandName" : "hl_flushPrintedDocuments" ,
        "s_sessionId"   : this.getSessionId()
    };

    if (documentsIds != undefined)
    {
        command.s_documentIds = documentsIds
    }

    return this.sendCommand(command, 15, resultCallback);
}

/**
 * @brief Get number of pending documents in the virtual printer directory
 *
 * IN
 * {
 * }
 * OUT
 * {
 *    "i_count" : ""
 * }
 */
DMPConnect.prototype.hl_getPrintedDocumentsCount = function( resultCallback )
{
    var command =
    {
        "s_commandName" : "hl_getPrintedDocumentsCount" ,
        "s_sessionId"   : this.getSessionId()
    }

    return this.sendCommand( command, 15 , resultCallback );
}

/**
  * @brief Get current list of printed documents
  *
  * OUT
  * {
  *     VirtualPrinterDocuments :
  *     [
  *         {
  *              "s_id" : "" ,
  *              "i_fileSize" : "",
  *              "s_fileName" : "",
  *              "s_contentInBase64" : ""
  *         }
  *     ]
  * }
  */
DMPConnect.prototype.hl_getPrintedDocuments = function( resultCallback )
{
    var command =
    {
        "s_commandName" : "hl_getPrintedDocuments" ,
        "s_sessionId"   : this.getSessionId()
    };

    return this.sendCommand( command, 15 , resultCallback );
}

/**
  * @brief Get next document from the Printed document list and remove it from the list.
  *
  * IN
  * OUT
  * {
  *      PrintedDocument
  *      {
  *          s_contentInBase64 : ""                  // Content of the document in base64
  *          s_fileName        : ""                  // Document name (without extension)
  *          i_fileSize        : ""                  // Size of the document (size corresponds to the unencoded document)
  *          s_id              : ""                  // Internal identifier of the document.
  *      },
  *      i_remainingDocumentCount : ""               // Number of remaining document in the list (after removing the returned document).
  * }
  *
  * Note: if no document is available, the structure PrintedDocument is not returned and i_remainingDocumentCount equals 0.
  */
DMPConnect.prototype.hl_getNextPrintedDocument = function (resultCallback)
{
    var command =
    {
        "s_commandName": "hl_getNextPrintedDocument",
        "s_sessionId": this.getSessionId()
    };

    return this.sendCommand(command, 30, resultCallback);
}

DMPConnect.prototype.hl_closeSession = function(resultCallback)
{
    var command = {
        "s_commandName" : "hl_closeSession",
        "s_sessionId"   : this.getSessionId()
    };

   return this.sendCommand(command, 20, resultCallback, false, (function(self){
       return function(result) {
           if (result.s_status == 'OK') {
               self._sessionId = "";
               self.resetState();
               self._refreshCallback();
           }
       }
   })(this));
};


/**
 * @brief Returned by hl_getPcscReaders.
 */
DMPConnect.ReaderSlotType =
{
    // 0 = invalid / uninitialized value.
    ST_NO_CARD     : 1,
    ST_VITALE_CARD : 2,
    ST_CPS_CARD    : 3,
    ST_OTHER       : 4,
    ST_UNKNOWN     : 5
};

/**
 * @brief Returned by hl_getPcscReaders.
 */
DMPConnect.ReaderAccessMode =
{
    // 0 = invalid / uninitialized value.
    AM_FULL_PCSC  : 1, // PC/SC reader
    AM_GALSS_PCSC : 2, // PC/SC reader relying on the GALSS server
    AM_GALSS_TL   : 3, // "Terminal Lecteur" reader.
    AM_GALSS_RAC  : 4  // "Referentiel Acces Cartes"-based reader.
} ;

/**
 * Specific JSON output (example):
 * {
 *   "Readers": [
 *     {
 *       "i_accessMode": 1,
 *       "i_slotType": 3,
 *       "s_accessMode": "Full PC/SC",
 *       "s_name": "SCM Microsystems Inc. SCR33x USB Smart Card Reader 1",
 *       "s_slotType": "CPS card"
 *     },
 *     {
 *       "i_accessMode": 1,
 *       "i_slotType": 2,
 *       "s_accessMode": "Full PC/SC",
 *       "s_name": "SCM Microsystems Inc. SCR35xx USB Smart Card Reader 0",
 *       "s_slotType": "Vitale card"
 *     },
 *     {
 *       "i_accessMode": 2,
 *       "i_slotType": 3,
 *       "s_accessMode": "GALSS/PCSC",
 *       "s_name": "PSS Reader on CPS",
 *       "s_slotType": "CPS card"
 *     },
 *     {
 *       "i_accessMode": 2,
 *       "i_slotType": 2,
 *       "s_accessMode": "GALSS/PCSC",
 *       "s_name": "Vitale",
 *       "s_slotType": "Vitale card"
 *     },
 *     {
 *       "i_accessMode": 2,
 *       "i_slotType": 2,
 *       "s_accessMode": "GALSS/PCSC",
 *       "s_name": "Log_SV",
 *       "s_slotType": "Vitale card"
 *     }
 *   ],
 *   "s_status": "OK"
 * }
 */
DMPConnect.prototype.hl_getPcscReaders = function(resultCallback)
{
    var command = {
        "s_commandName" : "hl_getPcscReaders",
        "s_sessionId"   : this.getSessionId()
    };

    return this.sendCommand(command, 15, resultCallback, false, (function (self) {
        return function (result) {
            if (result.s_status == 'OK') {
              self.setState('PcscReaders', true);
              self._refreshCallback();
            }
        }
    })(this));
};

/**
 * @param {number}  readerNumber    Indice of the reader in the list returned by hl_getPcscReaders().
 * @param {string}  readerName      Optional. PC/SC reader name (e.g. "Gemalto USB Smart Card Reader 0"),
 *                                   or a GALSS LAD name (e.g "CPS"). See DmpConnect specs for explanations.
 *                                  If it is set, 'readerNumber' is ignored.
 * @param {function} resultCallback The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_getCpxCard = function( readerNumber, readerName, resultCallback)
{
    var command = {
        "s_commandName"  : "hl_getCpxCard",
        "i_readerNumber" : readerNumber ? readerNumber : 0,
        "s_readerName"   : readerName ? readerName : "",
        "s_sessionId"    : this.getSessionId()
    };

    return this.sendCommand(command, 30, resultCallback, false, (function(self){
        return function(result) {
            if (result.s_status == 'OK') {
                self.setState('cpxCard', true);
                self._refreshCallback();
            }
        }
    })(this));
};

/**
 * @param {number}  readerNumber    Indice of the reader in the list returned by hl_getPcscReaders().
 * @param {string}  readerName      Optional. PC/SC reader name (e.g. "SCM Microsystems Inc. SCR331-DI Smart Card Reader 0"),
 *                                   or a GALSS LAD name (e.g "Vitale"). See DmpConnect specs for explanations.
 *                                  If it is set, 'readerNumber' is ignored.
 * @param {function} resultCallback The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_getVitaleCard = function( readerNumber, readerName, resultCallback)
{
    var command = {
        "s_commandName"  : "hl_getVitaleCard",
        "i_readerNumber" : readerNumber,
        "s_readerName"   : readerName,
        "s_sessionId"    : this.getSessionId()
    };

    return this.sendCommand(command, 10, resultCallback, false, (function(self){
        return function(result) {
            if (result.s_status == 'OK') {
                self.setState('vitaleCard', true);
                self._refreshCallback();
            }
        }
    })(this));
};

/**
 * hl_getCpxCard must have been called first.
 *
 * Specific JSON output:
 * {
 *   "s_signature"            : "<base64 content of the RSA signature>",
 *   "s_signatureCertificate" : "<PEM signature certificate>"
 * }
 *
 * @param {string} pinCode            E.g. "1234" for all test cards by default.
 * @param {string} data               Data to sign.
 * @param {function} resultCallback   The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_signWithCpxCard = function(pinCode, data, resultCallback)
{
    var command = {
        "s_commandName"  : "hl_signWithCpxCard",
        "s_pinCode"      : pinCode,
        "s_stringToSign" : data,
        "s_sessionId"    : this.getSessionId()
    };

    return this.sendCommand(command, 10, resultCallback);
};



/**
 * hl_getCpxCard must have been called first.
 *
 * IN :
 *  s_pinCode
 *  i_returnCertificates (*)
 *
 * Specific JSON output.
 * Example:
 * {
 *   "PracticeLocations": [
 *     {
 *       "s_practiceLocationActivity": "SA07",
 *       "s_practiceLocationHealthcareSettings": "SA07",
 *       "s_practiceLocationName": "CABINET DR GENE"
 *     }
 *   ],
 *   "i_remainingPinCodeInputs": 3,
 *   "s_given": "ALAIN",
 *   "s_internalId": "899900023351",
 *   "s_name": "GENE RPPS",
 *   "s_profession": "10",
 *   "s_professionOid": "1.2.250.1.71.1.2.7",
 *   "s_professionDescription" : "Médecin",
 *   "s_speciality": "SM26",
 *   "s_specialityDescription" : "Qualifié en médecine générale (SM)",
 *   "s_status": "OK"
 * }
 * @param {string}   pinCode          E.g. "1234" for all test cards by default.
 * @param {function} resultCallback   The callback that take the result from sendCommand
 */
DMPConnect.prototype.hl_readCpxCard = function(pinCode, resultCallback)
{
    var command = {
        "s_commandName"        : "hl_readCpxCard",
        "s_pinCode"            : pinCode,
        "i_returnCertificates" : 1, // Note: this is optional.
        "s_sessionId"          : this.getSessionId()
    };

    return this.sendCommand(command, 10, resultCallback, false, (function(self){
        return function(result) {
            if (result.s_status == 'OK') {
                self.setState('cpxCardRead', true);
                self._refreshCallback();
            }
        }
    })(this));
};

/**
  * @brief Get the status of the inserted CPx card.
  *
  * @note hl_getCpxCard must have been called first.
  *
  * OUT
  * {
  *     i_nbInputLeft   : Number of remaining PIN code inputs.
  *     s_validityDate  : Card expiration date.
  *     s_cardId        : Serial number of the card.
  *     i_cardStatus    : CPx status. @see DmpConnect.CpxCardStatus enum for values.
  * }
  */
 DMPConnect.prototype.hl_getCpxStatus = function( resultCallback )
 {
     var command = {
         "s_commandName" : "hl_getCpxStatus",
         "s_sessionId"   : this.getSessionId()
     };

     return this.sendCommand( command, 30 , resultCallback );
 }

/**
  * @brief Check if license of the current CPx user is valid to use this product.
  *
  * IN
  * {
  * }
  * OUT
  * {
  * }
  *
  * @note If licence is valid, this function simply returns OK, if not an error is generated and
  *       the detail associated in the answer could be checked to know why license check failed.
  */
DMPConnect.prototype.hl_checkUserLicenseRight = function (resultCallback)
{
    var command =
    {
        "s_commandName": "hl_checkUserLicenseRight",
        "s_sessionId": this.getSessionId()
    };

    return this.sendCommand(command, 10, resultCallback);
}

DMPConnect.VitaleStatus =
    {
        ApiError    : 0, // API error.
        MissingCard : 1, // Card is missing.
        Error       : 2, // Error: invalid faulty, invalid, saturated, unknown, silent, read error, etc.
        ProdCard    : 3, // Card found. Real valid card.
        TestCard    : 4, // Card found. Test card.
        DemoCard    : 5, // Card found. Demo card.
    };

/**
 * hl_getVitaleCard must have been called.
 *
 * Specific JSON output:
 * {
 *   "Patients": [
 *     {
 *       "s_birthday": "601020",
 *       "s_birthname": "",
 *       "s_certifiedNir": "",
 *       "s_given": "IGOR",
 *       "s_ins": "1793908334471761438991",
 *       "s_name": "AATIUN",
 *       "s_nir": "1601063220118 03"
 *     },
 *     {
 *       "s_birthday": "650930",
 *       "s_birthname": "",
 *       "s_certifiedNir": "",
 *       "s_given": "CECILE",
 *       "s_ins": "",
 *       "s_name": "AATIUN",
 *       "s_nir": "1601063220118 03"
 *     },
 *     {
 *       "s_birthday": "951210",
 *       "s_birthname": "",
 *       "s_certifiedNir": "",
 *       "s_given": "SERGE",
 *       "s_ins": "",
 *       "s_name": "AATIUN",
 *       "s_nir": "1601063220118 03"
 *     }
 *   ],
 *   "i_cardStatus": 5, // See VitaleStatus enum.
 *   "s_cardStatusText": "Card found. Demo card.",
 *   "s_status": "OK"
 * }
 */
DMPConnect.prototype.hl_readVitaleCard = function(resultCallback)
{
    var command = {
        "s_commandName" : "hl_readVitaleCard",
        "s_sessionId"   : this.getSessionId()
    };

    return this.sendCommand(command, 20, resultCallback, false, (function(self){
        return function(result) {
            if (result.s_status == 'OK') {
                self.setState('vitaleCardRead', true);
                self._refreshCallback();
            }
        }
    })(this));
};

/**
 * @brief Query several object parameters in one call.
 *        If no parameter is specified, all of the object dat is dumped.
 *
 * IN
 *    "i_objectHandle"   : handle of the object to query,
 *
 *    "stringIds"        : { Id0 : a parameter id of the object,
 *                           Id1 : another parameter id of the object
 *                           ...
 *                         },
 *    "stringBase64Ids"  : { Id0 : a parameter id of the object,
 *                           Id1 : another parameter id of the object
 *                           ...
 *                        },
 *    "integerIds"       : { Id0 : a parameter id of the object,
 *                           Id1 : another parameter id of the object
 *                           ...
 *                        },
 *    "floatIds"         : { Id0 : a parameter id of the object,
 *                           Id1 : another parameter id of the object
 *                           ...
 *                        },
 * OUT
 *
 * For an object, are returned:
 * - the object parameters ids and value as "id":value. See ObjectParameterName enum.
 * - the handle of the object in "handleId".
 * - the type of the object in "type". See HandleType enum.
 * Example below.
 * {
 *   "9": "GENE RPPS",
 *   "10": "ALAIN",
 *   "11": "10",
 *   "12": "1.2.250.1.71.1.2.7",
 *   "13": "SM26",
 *   "14": "899900023351",
 *   "86": 25,
 *   "handleId": 524290,
 *   "type": 9
 * }
 *
 * A object that is a List is dumped like that (81 = DocumentAuthors)
 * "81": {
 *   "handleId": 65562,
 *   "list": [
 *     {
 *       "9": "GENE RPPS",
 *       "10": "ALAIN",
 *       "11": "10",
 *       "12": "1.2.250.1.71.1.2.7",
 *       "13": "SM26",
 *       "14": "899900023351",
 *       "86": 25,
 *       "handleId": 524290,
 *       "type": 9
 *     }
 *   ],
 *   "type": 2
 * },
 */
DMPConnect.prototype.hl_getObjectParameters = function(command, resultCallback)
{
    command.s_commandName = "hl_getObjectParameters";
    command.s_sessionId   = this.getSessionId();

    return this.sendCommand(command, 2, resultCallback);
};

/**
 * @brief Return the internal handles used by the high level (HL) API.
 *
 * These handles:
 * - can be used with the low level API ;
 * - may not be initialized or be invalid. Their validity can be checked with checkValidity() and/or handleExists() for example.
 *
 * IN
 * OUT
 *   "i_error"            : The error object used by all HL functions. Should never be destroyed.
 *   "i_cpxReader"        : The CPx reader object (GALSS or PC/SC).
 *   "i_cpxCard"          : The CPx inserted in the CPx reader. Used by all CPx-related functions (hl_readCpxCard, hl_signWithCpxCard, all DMP functions, etc.)
 *   "i_vitaleCard"       : The Vitale card.
 *
 *   "i_practiceLocation" : DMP connector: Current practice location used for all DMP access.
 *   "i_authToken"        : DMP connector: Authentification token (CPS PKCS#11 token) used to authenticate the user and sign data.
 *   "i_connection"       : DMP connector: The TLS connection to the DMP.
 *   "i_caCert"           : DMP connector: The DMP CA certificate object.
 *   "i_user"             : DMP connector: The user object created from the CPx data.
 *   "i_dmpConnector"     : DMP connector object. The main object of the low level API.
 *
 *   "i_documentsFound"   : List of the documents found after a call to hl_findDocuments. Can be reused with getDocumentsExtraMetadata() for example.
 */
DMPConnect.prototype.hl_getSessionState = function(command, resultCallback)
{
    command = command || {};
    command.s_commandName = "hl_getSessionState";
    command.s_sessionId   = this.getSessionId();

    return this.sendCommand(command, 10, resultCallback);
};

/**
 * @brief Create a new connection object
 *
 * This handle can be used in sendHttpRequest for example
 *
 * IN
 *  "s_server"              : The URL domain (ex: http://google.fr) or IP (ex: 192.168.0.1)
 *  "i_requestTimeout"      : Timeout of the connection
 *  "s_proxyIpOrUrl"        : [Optional] Proxy IP (ex: 127.0.0.1) or URL (http://myproxy.com)
 *  "i_proxyPort"           : [Optional] Proxy port
 *  "s_proxyLogin"          : [Optional] Proxy login
 *  "s_proxyPassword"       : [Optional] Proxy password
 *  "s_caCertName"          : [Optional] Name of the CA cert file to use.
 *  "i_usePkcs11"           : [Optional] Use the current CPS card PKCS#11 token to establish the connection.
 * OUT
 *  "i_connectionHandle"    : handle of the connection
 *
 * @note: if no caCertName given, the default one, handles connections to :
 *          - "serveurs DMP de l'IGC Santé et de l'IGC CPS";
 *          - "serveurs MSS ASIP"
 *          - "Téléservices de la CNAMTS (TLSi)"
 */
DMPConnect.prototype.hl_createConnection = function(command, resultCallback)
{
    command = command || {};
    command.s_commandName = "hl_createConnection";
    command.s_sessionId   = this.getSessionId();

    return this.sendCommand(command, 10, resultCallback);
}

// ---------------------------------------------------------------------------------------------------------------------------------
// Temporary User-defined session data
// ---------------------------------------------------------------------------------------------------------------------------------
// Note:
//
//  Session data(s) consist(s) in user defined data stored as string in base 64, that can be get/set using uniques ID. The following
//  functionalities are available:
//
//  - User can add a new data (in this case, a corresponding ID will be returned
//  - User can update an existing data (using it's ID as reference)
//  - User can get value of an existing data
//  - User can remove an existing session data
//  - User can remove all session data
//
// There is a maximum size on all of the user session data:
//
//  - User cannot set a session data that exceed the maximum size
//  - User cannot add a session data that would result in a total of all it's session data above the maximum size
//  - User can query the current size of his session data
//  - User can query the maximum size allowed for the session data

/**
 * @brief Set or Update a user defined session data
 *
 * IN
 * {
 *      "s_sessionDataId"           : [Optional] User session data ID. If set try to update existing data, if not set create a new one.
 *      "s_sessionData64"           : Data to set/update (string in base64)
 * }
 * OUT
 * {
 *      "s_sessionDataId"           : ID of the data that have been set/updated
 * }
 *
 * @note If session data exceed maximum allowed size (@see hl_getMaximumSessionDataSize) data is not added and an error is returned
 * @note If session data size plus existing data size exceed maximum allowed size (@see hl_getMaximumSessionDataSize) data is not added and an error is returned
 * @note If sessionDataId is set but no existing data with this ID exists, data is not added and an error is returned
 */
DMPConnect.prototype.hl_setSessionData = function (command, resultCallback)
{
    command                 = command || {};
    command.s_commandName   = "hl_setSessionData";
    command.s_sessionId     = this.getSessionId();

    return this.sendCommand(command, 10, resultCallback);
}

/**
 * @brief Get an existing session data
 *
 * IN
 * {
 *      "s_sessionDataId"           : ID of the session data to get
 * }
 * OUT
 * {
 *      "s_sessionData64"           : content of the session data to get
 * }
 *
 * @note If session data does not exists, an error is returned
 */
DMPConnect.prototype.hl_getSessionData = function(command, resultCallback)
{
    command                 = command || {};
    command.s_commandName   = "hl_getSessionData";
    command.s_sessionId     = this.getSessionId();

    return this.sendCommand(command, 10, resultCallback);
}

/**
 * @brief Remove an existing data
 *
 * IN
 * {
 *      "s_sessionDataId"           : ID of the session data to remove
 * }
 * OUT
 *
 * @note If session data does not exists, an error is returned
 */
DMPConnect.prototype.hl_removeSessionData = function(command, resultCallback)
{
    command                 = command || {};
    command.s_commandName   = "hl_removeSessionData";
    command.s_sessionId     = this.getSessionId();

    return this.sendCommand(command, 10, resultCallback);
}

/**
 * @brief Clear all session data
 *
 * IN
 * OUT
 *
 */
DMPConnect.prototype.hl_clearSessionData = function(resultCallback)
{
    var command =
    {
        "s_commandName"     : "hl_clearSessionData",
        "s_sessionId"       : this.getSessionId()
    };

    return this.sendCommand(command, 10, resultCallback);
}


/**
 * @brief Get current size of the session data
 *
 * IN
 * OUT
 * {
 *      "i_sessionDataSize"       : Current size (in byte) of the session data(s)
 * }
 */
DMPConnect.prototype.hl_getSessionDataSize = function(resultCallback)
{
    var command =
    {
        "s_commandName"     : "hl_getSessionDataSize" ,
        "s_sessionId"       : this.getSessionId()
    };

    return this.sendCommand(command, 10, resultCallback);
}

/**
 * @brief Get maximum allowed size for the session data
 *
 * IN
 * OUT
 * {
 *      "hl_getMaximumSessionDataSize"  : Maximum allowed size (in byte) for the session data(s)
 * }
 */
DMPConnect.prototype.hl_getMaximumSessionDataSize = function(resultCallback)
{
    var command =
    {
        "s_commandName"     : "hl_getMaximumSessionDataSize" ,
        "s_sessionId"       : this.getSessionId()
    };

    return this.sendCommand(command, 10, resultCallback);
}

// ---------------------------------------------------------------------------------------------------------------------------------
// Persistant user data
// ---------------------------------------------------------------------------------------------------------------------------------
// Note:
//  Persistant data consist in data that can be read/save between sessions
//      Unlike session data:
//          - Data is not destroyed at the end of the session
//          - Data is unique (ie: only one block of persistant memory can be get/set), after each set, the existing data is overwritten
//
//      Like session data:
//          - a data can be saved or retrieved
//          - user can query the current size of the persistant data
//          - a maximum size limit exists for persistant data
//          - user can query the maximum allowed size
//
//  If user data exceed the maximum allowed size, an error is returned and the current persistent data is keep untouched.
//


/**
 * @brief set the persistant data
 *
 * IN
 * {
 *      "s_persistantData64"    : A base64 string containing the data to store in the persistant storage
 * }
 * OUT
 *
 * @note If the given data size exceed maximum allowed size (@see hl_getMaximumPersistantDataSize), an error is returned and the existing data size is kept untouched.
 * @note Existing persistant data is overwritten by this function.
 */
DMPConnect.prototype.hl_setPersistantData = function( command , resultCallback )
{
    command                 = command || {};
    command.s_commandName   = "hl_setPersistantData";
    command.s_sessionId     = this.getSessionId();

    return this.sendCommand(command, 10, resultCallback);
}

/**
 * @brief get persistant data
 *
 * IN
 *
 * OUT
 * {
 *      "s_persistantData64"    : A base64 string containing the data stored in the persistant storage
 * }
 *
 * @note If no existing persistant data exists, "s_persistantData64" will contains an empty string
 */
DMPConnect.prototype.hl_getPersistantData = function( resultCallback )
{
    var command =
    {
        "s_commandName"     : "hl_getPersistantData" ,
        "s_sessionId"       : this.getSessionId()
    };

    return this.sendCommand(command, 10, resultCallback);
}

/**
 * @brief Clear existing persistant data
 *
 * IN
 *
 * OUT
 */
DMPConnect.prototype.hl_clearPersistantData = function( resultCallback )
{
    var command =
    {
        "s_commandName"     : "hl_clearPersistantData" ,
        "s_sessionId"       : this.getSessionId()
    };

    return this.sendCommand(command, 10, resultCallback);
}

/**
 * @brief Get existing persistant data size
 *
 * IN
 *
 * OUT
 * {
 *      "i_persistantDataSize"      : Size (in byte) of the persistant data currently stored
 * }
 */
DMPConnect.prototype.hl_getPersistantDataSize = function( resultCallback )
{
    var command =
    {
        "s_commandName"     : "hl_getPersistantDataSize" ,
        "s_sessionId"       : this.getSessionId()
    };

    return this.sendCommand(command, 10, resultCallback);
}


/**
 * @brief Get maximum allowed size of the persistant data
 *
 * IN
 *
 * OUT
 * {
 *      "i_maximumPersistantDataSize"       : Maximum size (in byte) of the persistant data that can be stored
 * }
 */
DMPConnect.prototype.hl_getMaximumPersistantDataSize = function( resultCallback )
{
    var command =
    {
        "s_commandName"     : "hl_getMaximumPersistantDataSize" ,
        "s_sessionId"       : this.getSessionId()
    };

    return this.sendCommand(command, 10, resultCallback);
}

// ---------------------------------------------------------------------------------------------------------------------------------
// Low level API functions.
// ---------------------------------------------------------------------------------------------------------------------------------
// Note:
//   The reference documentation of the functions below is available in the C headers and the C API specification PDF.
//   JavaScript and C functions names are the same.
//
//   Most of the time, the expected JSON parameter have the same name than the C parameters prefixed with the parameter type ('i_', 's_', etc.).
//
//   Ex.: D_HANDLE getObjectFromList( D_HANDLE list, unsigned int position, D_HANDLE error );
//   Expects the following parameters:
//   {
//     "i_list" : xxx,
//     "i_position": xxx
//   }
//
//   The last parameter 'D_HANDLE error'' is always omitted: the internal session error object is used.
//
//   Example of call:
//       dmpConnectInstance.getParameterString( { i_globalParameter: DMPConnect.GlobalParameterName.VersionName } );

/**
 * @brief Helper function for low level API function calls.
 * Can be used directly, however most of the available calls are wrapped in a specific function.
 *
 * For command taking an 'i_globalParameter' parameter, see DMPConnect.GlobalParameterName.
 *
 * For command taking an 'i_parameter' parameter, see DMPConnect.ObjectParameterName.
 * In this last case, the function will also need the object handle in i_object (decimal value).
 *
 * @param {string}   commandName       Id of the command. Ex.: "getParameterString", etc.
 * @param {object}   commandParameters Object holding the function parameters in its properties.
 *                                     Ex.: { "i_globalParameter": DMPConnect.GlobalParameterName.VersionName }
 * @param {function} resultCallback    The callback that take the result from sendCommand
 */
DMPConnect.prototype.lowLevelApiFunctionCall = function(commandName, commandParameters, resultCallback)
{
    var command = {};
    command.s_commandName = commandName;
    command.s_sessionId   = this.getSessionId();

    // Merge the function-specific parameters.
    for(var paramName in commandParameters)
    {
        command[paramName] = commandParameters[paramName];
    }

    // Execute the command.
    return this.sendCommand(command, 10, resultCallback);
};

// IN : i_globalParameter, i_resultInBase64
// OUT: s_utf8String, s_utf8StringSize
DMPConnect.prototype.getParameterString           = function( commandParameters, resultCallback ) {this.lowLevelApiFunctionCall( "getParameterString"          , commandParameters, resultCallback ); };

// IN : i_globalParameter
// OUT: i_returnValue
DMPConnect.prototype.getParameterStringSize       = function( commandParameters, resultCallback ) {this.lowLevelApiFunctionCall( "getParameterStringSize"      , commandParameters, resultCallback ); };

// IN : i_globalParameter
// OUT: i_returnValue
DMPConnect.prototype.getParameterInt              = function( commandParameters, resultCallback ) {this.lowLevelApiFunctionCall( "getParameterInt"             , commandParameters, resultCallback ); };

// IN : i_globalParameter
// OUT: i_returnValue
DMPConnect.prototype.getObjectParameterStringSize = function( commandParameters, resultCallback ) {this.lowLevelApiFunctionCall( "getObjectParameterStringSize", commandParameters, resultCallback ); };

// IN : i_parameter, i_object, i_resultInBase64
// OUT: s_utf8String, s_utf8StringSize
DMPConnect.prototype.getObjectParameterString     = function( commandParameters, resultCallback ) {this.lowLevelApiFunctionCall( "getObjectParameterString"    , commandParameters, resultCallback ); };

// IN : i_parameter, i_object
// OUT: i_returnValue
DMPConnect.prototype.getObjectParameterInt        = function( commandParameters, resultCallback ) {this.lowLevelApiFunctionCall( "getObjectParameterInt"       , commandParameters, resultCallback ); };

// IN : i_parameter, i_object
// OUT: f_returnValue
DMPConnect.prototype.getObjectParameterFloat      = function( commandParameters, resultCallback ) {this.lowLevelApiFunctionCall( "getObjectParameterFloat"     , commandParameters, resultCallback ); };

// IN : i_list, i_position
// OUT: i_returnValue
DMPConnect.prototype.getObjectFromList            = function( commandParameters, resultCallback ) {this.lowLevelApiFunctionCall( "getObjectFromList"           , commandParameters, resultCallback ); };

// IN : i_list
// OUT: i_returnValue
DMPConnect.prototype.getListSize                  = function( commandParameters, resultCallback ) {this.lowLevelApiFunctionCall( "getListSize"                 , commandParameters, resultCallback ); };

// IN : i_list, i_position
// OUT: i_returnValue
DMPConnect.prototype.copyObjectFromList           = function( commandParameters, resultCallback ) {this.lowLevelApiFunctionCall( "copyObjectFromList"          , commandParameters, resultCallback ); };

// IN : i_handle
// OUT: i_returnValue
DMPConnect.prototype.handleExists                 = function( commandParameters, resultCallback ) {this.lowLevelApiFunctionCall( "handleExists"                , commandParameters, resultCallback ); };

// IN : i_handle
// OUT: i_returnValue
DMPConnect.prototype.checkValidity                = function( commandParameters, resultCallback ) {this.lowLevelApiFunctionCall( "checkValidity"               , commandParameters, resultCallback ); };

// IN : i_handle
// OUT: i_returnValue
DMPConnect.prototype.getHandleType                = function( commandParameters, resultCallback ) {this.lowLevelApiFunctionCall( "getHandleType"               , commandParameters, resultCallback ); };

// IN : i_type
// OUT: i_returnValue
DMPConnect.prototype.createObject                 = function( commandParameters, resultCallback ) {this.lowLevelApiFunctionCall( "createObject"                , commandParameters, resultCallback ); };

// IN : i_handle
DMPConnect.prototype.releaseObject                = function( commandParameters, resultCallback ) {this.lowLevelApiFunctionCall( "releaseObject"               , commandParameters, resultCallback ); };

// IN : i_list
DMPConnect.prototype.releaseList                  = function( commandParameters, resultCallback ) {this.lowLevelApiFunctionCall( "releaseList"                 , commandParameters, resultCallback ); };

// IN : i_error
DMPConnect.prototype.releaseErrorObject           = function( commandParameters, resultCallback ) {this.lowLevelApiFunctionCall( "releaseErrorObject"          , commandParameters, resultCallback ); };

// Currently for debug only.
// ---------------------------------------------------------------------------------------------------------------------------------



/**
 * @brief Return either the server log, the log of DmpConnect or both logs.
 *
 * Log data is returned in base 64, with '\n' as new line delimiter.
 *
 * All input parameters below are optional.
 * The default behaviour (no parameters defined) is to return the last 50 lines of both logs.
 *
 * IN
 *   i_getServerLog             : Optional. 1: retreive the log. 0: do not retreive it. Default: 1
 *   i_getDmpConnectLog         : idem.
 *   i_serverLogNbLastLines     : Optional. Default: 50
 *   i_dmpConnectLogNbLastLines :Optional.  Default: 50
 * OUT
 *   s_serverLogBase64     : Defined if i_getServerLog     != 0
 *   s_dmpConnectLogBase64 : Defined if i_getDmpConnectLog != 0
 */
DMPConnect.prototype.getLogsTail = function(command, resultCallback)
{
    command = command || {};
    command.s_commandName = "getLogsTail";

    return this.sendCommand(command, 4, resultCallback);
};

/**
 * @brief Return either the server log, the log of DmpConnect or both logs.
 * This is the log associated with the current JS process where the session resides.
 *
 * Log data is returned in base 64, with '\n' as new line delimiter.
 *
 * All input parameters below are optional.
 * The default behaviour (no parameters defined) is to return the last 50 lines of both logs.
 *
 * IN
 *   i_getServerLog             : Optional. 1: retreive the log. 0: do not retreive it. Default: 1
 *   i_getDmpConnectLog         : idem.
 *   i_serverLogNbLastLines     : Optional. Default: 50
 *   i_dmpConnectLogNbLastLines :Optional.  Default: 50
 * OUT
 *   s_serverLogBase64     : Defined if i_getServerLog     != 0
 *   s_dmpConnectLogBase64 : Defined if i_getDmpConnectLog != 0
 */
DMPConnect.prototype.hl_getSessionLogsTail = function( command, resultCallback )
{
    command = command || {};
    command.s_commandName = "hl_getSessionLogsTail";
    command.s_sessionId   = this.getSessionId();

    return this.sendCommand(command, 4, resultCallback);
}

/**
  * @brief Get current log levels.
  *
  * IN 
  * {
  *
  * }
  * OUT 
  * {
  *     i_dmpConnectLogLevel
  *     i_dmpConnectJsLogLevel
  * }
  */
DMPConnect.prototype.getLogLevels = function( command, resultCallback )
{
    command = command || {};
    command.s_commandName = "getLogLevels";

    return this.sendCommand( command, 4 , resultCallback );
}

/**
  * @brief Set log levels.
  *
  * IN 
  * {
  *     i_dmpConnectLogLevel
  *     i_dmpConnectJsLogLevel
  * }
  * OUT 
  * {
  *        
  * }
  */
DMPConnect.prototype.setLogLevels = function( command, iDmpConnectLevel, iDmpConnectJsLevel, resultCallback )
{
    command = command || {};

    command.s_commandName          = "setLogLevels";
    command.i_dmpConnectLogLevel   = iDmpConnectLevel;
    command.i_dmpConnectJsLogLevel = iDmpConnectJsLevel;

    return this.sendCommand( command, 4 , resultCallback );
}

/**
 * @brief Return either the server log, the log of DmpConnect or both logs.
 * This is the log associated with the current JS process where the session resides.
 *
 * Log data is returned in base 64, with '\n' as new line delimiter.
 *
 * All input parameters below are optional.
 * The default behaviour (no parameters defined) is to return the last 50 lines of both logs.
 *
 * IN
 *   i_getServerLog             : Optional. 1: retreive the log. 0: do not retreive it. Default: 1
 *   i_getDmpConnectLog         : idem.
 *   i_serverLogNbLastLines     : Optional. Default: 50
 *   i_dmpConnectLogNbLastLines :Optional.  Default: 50
 * OUT
 *   s_serverLogBase64     : Defined if i_getServerLog     != 0
 *   s_dmpConnectLogBase64 : Defined if i_getDmpConnectLog != 0
 */
DMPConnect.prototype.hl_getSessionLogsTail = function( command, resultCallback )
{
    command = command || {};
    command.s_commandName = "hl_getSessionLogsTail";
    command.s_sessionId   = this.getSessionId();

    return this.sendCommand(command, 4, resultCallback);
}

/**
 * @brief Convert a date to standard format:
 *     - if it's already standard, return same date
 *     - if it's lunar, convert it to standard date
 *
 * @param {string} aDate    Input date (YYMMDD or DDMMYYYY)
 *
 * IN
 *      "s_date"
 * OUT
 *      "s_date"
 */
DMPConnect.prototype.hl_convertLunarDateToStandardDate = function(aDate, resultCallback)
{
    command = {};
    command.s_date          = aDate;
    command.s_commandName   = "hl_convertLunarDateToStandardDate";
    command.s_sessionId     = this.getSessionId();

    return this.sendCommand(command, 5, resultCallback);
};

/**
 * @brief Compute century for a birth date without century
 *
 * @param {string} aDate        Date from which century must be computed.
 * @param {int} aQuality        Quality of the patient, important value is 6 that indicate that patient is a child.
 *
 * IN
 *      "s_date"
 *      "i_quality"
 * OUT
 *      "i_century"
 */
DMPConnect.prototype.hl_computeBirthDayCentury = function(aDate, aQuality, resultCallback)
{
    command = {};
    command.s_date          = aDate;
    command.i_quality       = aQuality;
    command.s_commandName   = "hl_computeBirthDayCentury"
    command.s_sessionId     = this.getSessionId();

    return this.sendCommand(command, 5, resultCallback);
};

/**
  * @brief Regularize a birth date if needed.
  *         Regularization perform the two steps:
  *             1. It standardize date, if it's a lunar one ;
  *             2. It adds a century if date is in format YYMMDD.
  *
  * @param {string} aDate       Input date (format YYMMDD or DDMMYYYY).
  * @param {int}    aQuality    Quality of the patient, important value is 6 that indicate that patient is a child.
  *
  * IN
  *     s_date
  *     i_quality
  * OUT
  *     s_date
  */
DMPConnect.prototype.hl_regularizeBirthDateIfNeeded = function(aDate, aQuality, resultCallback)
{
    command = {};
    command.s_date          = aDate;
    command.i_quality       = aQuality;
    command.s_commandName   = "hl_regularizeBirthDateIfNeeded"
    command.s_sessionId     = this.getSessionId();

    return this.sendCommand(command, 5, resultCallback);
}

// ---------------------------------------------------------------------------------------------------------------------------------
// definitions-common.h
// ---------------------------------------------------------------------------------------------------------------------------------

DMPConnect.DMPCONNECT_VERSION_MAJOR =  3;
DMPConnect.DMPCONNECT_VERSION_MINOR =  0;
DMPConnect.DMPCONNECT_VERSION_BUILD =  0;

/**
  * @brief Log levels.
  *
  * For use with setupLogOutput().
  *
  */
DMPConnect.LogVerbosity =
{
    Disabled : 0x00, ///< No output at all.
    Fatal    : 0x01, ///< Only fatal error messages are shown.
    Errors   : 0x02, ///< Previous + standard error messages.
    Warnings : 0x03, ///< Previous + warning error messages.
    Infos    : 0x04, ///< Previous + informative messages.
    Dev      : 0x05, ///< Previous + debug infos for developers. Note: to be able to see details about network-related operations (cURL, OpenSSL) this log level must be set at the initialization step.
};

DMPConnect.ConsoleOutputMode =
{
    ConsoleOutputEnabled  : 0x10000, ///< Log output is done into the console and the log file.
    ConsoleOutputDisabled : 0x20000  ///< Log output only done into the log file.
};

/**
  * @brief API objects types.
  */
DMPConnect.HandleType =
{
    /// Common handle types.
    HandleType_Undefined            : 1,
    HandleType_List                 : 2,
    HandleType_Error                : 3,

    /// Galss support handle types.
    HandleType_GalssResource        : 4,
    HandleType_Galss                : 5,
    HandleType_PcscResource         : 107, // Since v.2.0.0
    HandleType_Pcsc                 : 108, // Since v.2.0.0

    /// Vitale support handle types.
    HandleType_Vitale               : 6,
    HandleType_VitalePatient        : 7,

    /// Common CPx & ES support handle types.
    HandleType_TimeProvider         : 8,
    HandleType_HP                   : 9,
    HandleType_SubmissionSet        : 10,
    HandleType_Document             : 11,
    HandleType_Connection           : 12,
    HandleType_DmpConnector         : 13,
    HandleType_Certificate          : 14,
    HandleType_PracticeLocation     : 15,
    HandleType_EventCode            : 19,      // Since v.1.1.6.

    /// CPx support handle types.
    HandleType_CPx                  : 16,
    HandleType_CPxPkcsToken         : 17,
    HandleType_Filter               : 18
};

/**
  * @brief Global API parameters.
  *
  * Values can be retreived with:
  * - getParameterStringSize(), getParameterString() for strings parameters.
  * - getParameterInt() for integer, bool or D_HANDLE parameters.
  */
DMPConnect.GlobalParameterName =
{
    // String parameters.
    // -------------------------------------------
    VersionName                             : 1,

    PatientWebAccessPdfStoragePath          : 2,
    PatientWebAccessPdfTemplateFilename     : 3,
    PatientWebAccessPdfDefaultUrl           : 4,
    CryptolibCpsDllFilename                 : 5, // Renamed in v2.0.0. Previous name "Pkcs11ModulePath".

    // Integer parameters.
    // -------------------------------------------
    VersionMajor                            : 6,
    VersionMinor                            : 7,
    VersionBuild                            : 8,

    // D_HANDLE parameters.
    // -------------------------------------------
    CurrentTimeProvider                     : 9, // Handle type: Time Provider.

    // Boolean parameters.
    // -------------------------------------------
    Pkcs11EngineIsInitialized               : 10
};

/**
  * @brief Objects parameters.
  *
  * Values can be retreived with:
  * - getObjectParameterString(), getObjectParameterStringSize() for strings parameters.
  * - getObjectParameterInt() for integers, D_HANDLE, enums or boolean parameters.
  * - getObjectParameterFloat() for float parameters.
  */
DMPConnect.ObjectParameterName =
{
    // String parameters.
    // -------------------------------------------
    ErrorDescription                    : 1,
    ErrorContext                        : 2,
    ErrorExtendedInformations           : 3,
    TimeProviderIp                      : 4,
    ConnectionUrl                       : 5,
    ConnectionProxyIp                   : 6,
    ConnectionProxyLogin                : 7,
    ConnectionProxyPassword             : 8,
    HpName                              : 9,
    HpGiven                             : 10,
    HpProfession                        : 11,
    HpProfessionOid                     : 12,
    HpProfessionDescription             : 133,
    HpSpeciality                        : 13,
    HpSpecialityDescription             : 134,
    HpInternalId                        : 14,
    PracticeLocationName                : 15,
    PracticeLocationActivity            : 16,
    PracticeLocationPracticeSettings    : 17,
    PracticeLocationHealthcareSettings  : 112, // Since v.2.2.1. Set if a compatible activity sector is found in a CPx card. Empty otherwise.
    DmpConnectorUniqueId                : 18,
    DmpConnectorDumpPath                : 19,
    DmpConnectorLocalPatientRootOid     : 94,  // Since v.1.1.4.
    DocumentUniqueId                    : 20,
    DocumentContent                     : 21,
    DocumentCreationDate                : 48,  // UTC date of the medical document creation. Available in ES mode since v.2.0.0.
    DocumentServiceStartDate            : 49,  // UTC date of the start of the medical act.  Idem.
    DocumentServiceStopDate             : 50,  // UTC date of the end   of the medical act.  Idem.
    DocumentCda_CrBioSetIdRoot          : 104, // Since v.1.5.1. Extra CDA metadata for "Compte Rendu de Biologie".
    DocumentCda_CrBioSetIdExtension     : 105, // Since v.1.5.1. Idem.
    DocumentCda_CrBioVersionNumber      : 106, // Since v.1.5.1. Idem.
    SubmissionSetTitle                  : 23,
    SubmissionSetDescription            : 24,
    SubmissionSetLocalPatientId         : 93,  // Since v.1.1.4.
    EventCodeCode                       : 96,  // Since v.1.1.6.
    EventCodeDescription                : 97,  // Since v.1.1.6.

    CertificateFilename                 : 25,
    CertificatePassPhrase               : 26,

    GalssResourceName                   : 27,
    GalssFilename                       : 28,
    PcscResourceName                    : 109,

    VitaleApiLecFilename                : 29,
    VitaleCurrentReaderName             : 113, // Since v.2.0.0.
    VitaleXmlContent                    : 30,
    VitalePatientName                   : 31,
    VitalePatientBirthname              : 32,
    VitalePatientGiven                  : 33,
    VitalePatientBirthday               : 34,  // Format: either YYMMDD or DDMMYYYY (since v2.0.0).
    VitalePatientIns                    : 35,  // INS-C if any (see VitalePatientInsAvailable).
    VitalePatientXml                    : 36,
    VitalePatientNir                    : 37,
    VitalePatientCertifiedNir           : 38,

    FilterCategoriesList                : 39,  // Filter categories list (';'-separated).
    FilterFormatsList                   : 40,  // Filter formats    list (';'-separated).
    FilterPracticesList                 : 41,  // Filter practices  list (';'-separated).
    FilterBottomCreationDate            : 42,
    FilterTopCreationDate               : 43,
    FilterBottomServiceStartDate        : 44,
    FilterTopServiceStartDate           : 45,
    FilterBottomSubmissionDate          : 46,
    FilterTopSubmissionDate             : 47,
    DocumentTitle                       : 51,
    DocumentDescription                 : 52,
    DocumentClassCode                   : 53,
    DocumentTypeCode                    : 54,
    DocumentHealthCareFacilityTypeCode  : 55,
    DocumentPracticeSettingCode         : 56,
    DocumentPatientIns                  : 58,
    DocumentHash                        : 59,
    DocumentXDSMetadata                 : 60,
    DocumentSubmissionDate              : 61,
    DocumentUUId                        : 102, // Since v.1.2.5
    DocumentPreviousUUId                : 62,  // Renamed in v.1.2.5
    DocumentNextUUId                    : 63,  // Renamed in v.1.2.5
    DocumentCDAContent                  : 103, // Since v.1.2.6
    CpxSerialNumber                     : 99,  // Since v.1.2.0
    CpxReaderName                       : 110, // Since v.2.0.0
    CpxValidityDate                     : 111, // Since v.2.0.0

    // Integer parameters.
    // -------------------------------------------
    ErrorCode                           : 64,

    TimeProviderPort                    : 66,
    ConnectionTimeOut                   : 67,
    ConnectionProxyPort                 : 68,

    DocumentSize                        : 70,

    // Boolean parameters.
    // -------------------------------------------
    DocumentPdfFormatConversionEnabled  : 71,

    VitalePatientInsAvailable           : 72,

    // Float parameters.
    // -------------------------------------------
    TimeProviderSynchronizationDifference : 74,

    // D_HANDLE parameters.
    // -------------------------------------------
    ConnectionAuthenticationToken       : 75,  // Handle type: Certificate.
    ConnectionCaCertificate             : 76,  // Handle type: Certificate.
    DmpConnectorConnection              : 77,  // Handle type: Connection.
    DmpConnectorPracticeLocation        : 78,  // Handle type: Practice location.
    DmpConnectorUser                    : 79,  // Handle type: HP.
    DocumentPerformer                   : 80,  // Handle type: HP.
    DocumentAuthors                     : 81,  // Handle type: List.
    DocumentEventCodes                  : 95,  // Handle type: List. Added since 1.1.6.
    SubmissionSetDocuments              : 82,  // Handle type: List.

    PracticeLocationSignatureCertificate: 83, // Handle type: Certificate. ES mode only.

    // Enum parameters.
    // -------------------------------------------
    ErrorTypeCode                       : 65,  // enum: ErrorType.

    GalssResourceEntryType              : 84,  // enum: GalssResourceType.
    GalssResourceEntryProtocol          : 85,  // enum: GalssResourceProtocol.

    EventCodeClassification             : 98,  // enum: EventCodeType.  Since v.1.1.6.
    VitaleCardType                      : 101, // enum: CardDemoStatus. Since v.1.1.5. Real card or Demo card.
    HpAuthenticationMode                : 86,  // enum: UserAuthenticationMode.
    DocumentPerformerRole               : 87,  // enum: PerformerRole.

    CPxCardType                         : 100, // enum: CardDemoStatus. Since v.1.2.0. Real card or Demo card.
    CpxType                             : 69,  // enum: CPxType.
    FilterDocumentStatus                : 88,  // enum: DocumentStatus.
    FilterDocumentVisibility            : 89,  // enum: DocumentVisibility.
    Document_Status                     : 90,  // enum: DocumentStatus.
    Document_Visibility                 : 91,  // enum: DocumentVisibility.
    Document_Format                     : 92   // enum: DocumentFormat.
};

/**
  * @brief CPx types: "CPS", "CPF", "CPE", "CPA" or "CSA".
  *
  * Only CPS or CPF cards can be used to get DMP documents.
  * See DMPConnect.ObjectParameterName.CpxType.
  *
  */
DMPConnect.CPxType =
{
    CPS : 1,
    CPF : 2,
    CPE : 3,
    CPA : 4
};

/**
  * @brief Type of CPx or Vitale card: real card or demonstration/dev card?
  * See See DMPConnect.ObjectParameterName.CPxCardType and DMPConnect.ObjectParameterName.VitaleCardType
  */
DMPConnect.CardDemoStatus =
{
    Real : 1,
    Demo : 2
};

/**
  * @brief Type of the identifier of the HP.
  *
  * These values are used for i_internalIdType or i_hpInternalIdType of HP structures ;
  * They can be defined for custom author ;
  * They are returned for all authors after a document search.
  */
DMPConnect.IdentifierType =
{
    // Patient identifier
    IdentifierType_PatientIns                : 1 ,
    IdentifierType_PatientInsC               : 2 ,
    // National identifier
    IdentifierType_NationalIdentifierRpps    : 3 ,
    IdentifierType_NationalIdentifierAdeli   : 4 ,
    IdentifierType_NationalIdentifierSirius  : 5 ,
    IdentifierType_NationalIdentifierStudent : 6 ,
    // Internal identifier (ID is internal to a given structrure)
    // For id relative to a different structures of the current user:
    IdentifierType_InternalIdentifierAdeli   : 7 ,
    IdentifierType_InternalIdentifierFiness  : 8 ,
    IdentifierType_InternalIdentifierSiren   : 9 ,
    IdentifierType_InternalIdentifierSiret   : 10 ,
    IdentifierType_InternalIdentifierRpps    : 11 ,
    // For id relative to the current structure:
    IdentifierType_InternalIdentifier        : 12
};
