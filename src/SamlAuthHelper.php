<?php

namespace Rybel\backbone;

class SamlAuthHelper extends AuthHelper {

    private $samlAuth;
    private $samlSettings;

    public function __construct(string $spBaseUrl, string $idpBaseUrl, string $idpCert, string $spCert, string $spKey, bool $debug = false) {
        session_start();
        
        $settingsInfo = array (
            // Enable debug mode (to print errors)
            'debug' => $debug,
        
            // Set a BaseURL to be used instead of try to guess
            // the BaseURL of the view that process the SAML Message.
            // Ex. http://sp.example.com/
            //     http://example.com/sp/
            'baseurl' => $spBaseUrl,
        
            // Service Provider Data that we are deploying
            'sp' => array (
                // Identifier of the SP entity  (must be a URI)
                'entityId' => $spBaseUrl . '?smd',
                // Specifies info about where and how the <AuthnResponse> message MUST be
                // returned to the requester, in this case our SP.
                'assertionConsumerService' => array (
                    // URL Location where the <Response> from the IdP will be returned
                    'url' => $spBaseUrl . '?acs',
                    // SAML protocol binding to be used when returning the <Response>
                    // message.  SAML Toolkit supports for this endpoint the
                    // HTTP-POST binding only
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                ),
                // Specifies info about where and how the <Logout Response> message MUST be
                // returned to the requester, in this case our SP.
                'singleLogoutService' => array (
                    // URL Location where the <Response> from the IdP will be returned
                    'url' => $spBaseUrl . '?sls',
                    // SAML protocol binding to be used when returning the <Response>
                    // message.  SAML Toolkit supports for this endpoint the
                    // HTTP-Redirect binding only
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                // Specifies constraints on the name identifier to be used to
                // represent the requested subject.
                // Take a look on lib/Saml2/Constants.php to see the NameIdFormat supported
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
        
                // Usually x509cert and privateKey of the SP are provided by files placed at
                // the certs folder. But we can also provide them with the following parameters
                'x509cert' => $spCert,
                'privateKey' => $spKey,
            ),
        
            // Identity Provider Data that we want connect with our SP
            'idp' => array (
                // Identifier of the IdP entity  (must be a URI)
                'entityId' => $idpBaseUrl,
                // SSO endpoint info of the IdP. (Authentication Request protocol)
                'singleSignOnService' => array (
                    // URL Target of the IdP where the SP will send the Authentication Request Message
                    'url' => $idpBaseUrl . '/protocol/saml',
                    // SAML protocol binding to be used when returning the <Response>
                    // message.  SAML Toolkit supports for this endpoint the
                    // HTTP-Redirect binding only
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                // SLO endpoint info of the IdP.
                'singleLogoutService' => array (
                    // URL Location of the IdP where the SP will send the SLO Request
                    'url' => $idpBaseUrl . '/protocol/saml',
                    // URL location of the IdP where the SP will send the SLO Response (ResponseLocation)
                    // if not set, url for the SLO Request will be used
                    'responseUrl' => '',
                    // SAML protocol binding to be used when returning the <Response>
                    // message.  SAML Toolkit supports for this endpoint the
                    // HTTP-Redirect binding only
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ),
                // Public x509 certificate of the IdP
                'x509cert' => $idpCert,
            ),
        );
        
        $this->samlAuth = new OneLogin\Saml2\Auth($settingsInfo);
        $this->samlSettings = new OneLogin\Saml2\Settings($settingsInfo, true);
    }

    public function isLoggedIn() {
        return isset($_SESSION['samlNameId']) || $_SERVER['REMOTE_ADDR'] == '127.0.0.1';
    }

    public function isAdmin() {
        return $_SESSION['samlUserdata']['admin'];
    }

    public function processSamlInput() {
        if (isset($_GET['sso'])) {
            $this->samlAuth->login();
        } else if (isset($_GET['acs'])) {
            if (isset($_SESSION) && isset($_SESSION['AuthNRequestID'])) {
                $requestID = $_SESSION['AuthNRequestID'];
            } else {
                $requestID = null;
            }
        
            $this->samlAuth->processResponse($requestID);
        
            $errors = $this->samlAuth->getErrors();
        
            if (!empty($errors)) {
                echo '<p>',implode(', ', $errors),'</p>';
                if ($this->samlAuth->getSettings()->isDebugActive()) {
                    echo '<p>'.htmlentities($this->samlAuth->getLastErrorReason()).'</p>';
                }
            }
        
            if (!$this->samlAuth->isAuthenticated()) {
                echo "<p>Not authenticated</p>";
            } else {
                $_SESSION['samlUserdata'] = $this->samlAuth->getAttributes();
                $_SESSION['samlNameId'] = $this->samlAuth->getNameId();
                $_SESSION['samlNameIdFormat'] = $this->samlAuth->getNameIdFormat();
                $_SESSION['samlNameIdNameQualifier'] = $this->samlAuth->getNameIdNameQualifier();
                $_SESSION['samlNameIdSPNameQualifier'] = $this->samlAuth->getNameIdSPNameQualifier();
                $_SESSION['samlSessionIndex'] = $this->samlAuth->getSessionIndex();
                unset($_SESSION['AuthNRequestID']);
                if (isset($_POST['RelayState']) && OneLogin\Saml2\Utils::getSelfURL() != $_POST['RelayState']) {
                    // To avoid 'Open Redirect' attacks, before execute the 
                    // redirection confirm the value of $_POST['RelayState'] is a // trusted URL.
                    $this->samlAuth->redirectTo($_POST['RelayState']);
                }
            }
        } else if (isset($_GET['slo'])) {
            $returnTo = null;
            $parameters = array();
            $nameId = null;
            $sessionIndex = null;
            $nameIdFormat = null;
            $samlNameIdNameQualifier = null;
            $samlNameIdSPNameQualifier = null;
        
            if (isset($_SESSION['samlNameId'])) {
                $nameId = $_SESSION['samlNameId'];
            }
            if (isset($_SESSION['samlNameIdFormat'])) {
                $nameIdFormat = $_SESSION['samlNameIdFormat'];
            }
            if (isset($_SESSION['samlNameIdNameQualifier'])) {
                $samlNameIdNameQualifier = $_SESSION['samlNameIdNameQualifier'];
            }
            if (isset($_SESSION['samlNameIdSPNameQualifier'])) {
                $samlNameIdSPNameQualifier = $_SESSION['samlNameIdSPNameQualifier'];
            }
            if (isset($_SESSION['samlSessionIndex'])) {
                $sessionIndex = $_SESSION['samlSessionIndex'];
            }
        
            $this->samlAuth->logout($returnTo, $parameters, $nameId, $sessionIndex, false, $nameIdFormat, $samlNameIdNameQualifier, $samlNameIdSPNameQualifier);
        } else if (isset($_GET['sls'])) {
            if (isset($_SESSION) && isset($_SESSION['LogoutRequestID'])) {
                $requestID = $_SESSION['LogoutRequestID'];
            } else {
                $requestID = null;
            }
        
            $this->samlAuth->processSLO(false, $requestID);
            $errors = $this->samlAuth->getErrors();
            if (empty($errors)) {
                session_destroy();
                header("Location: ?");
                die();
            } else {
                echo '<p>', htmlentities(implode(', ', $errors)), '</p>';
                if ($this->samlAuth->getSettings()->isDebugActive()) {
                    echo '<p>'.htmlentities($this->samlAuth->getLastErrorReason()).'</p>';
                }
            }
        } else if (isset($_GET['smd'])) {
            try {
                $metadata = $this->samlSettings->getSPMetadata();
                $errors = $this->samlSettings->validateMetadata($metadata);
                if (empty($errors)) {
                    header('Content-Type: text/xml');
                    echo $metadata;
                } else {
                    throw new OneLogin\Saml2\Error(
                        'Invalid SP metadata: '.implode(', ', $errors),
                        OneLogin\Saml2\Error::METADATA_SP_INVALID
                    );
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            die();
        }
    }
}