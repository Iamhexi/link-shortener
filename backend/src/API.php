<?php
require_once 'Authenticator.php';
require_once 'ShortenedLinksAdder.php';
require_once 'ShortenedLinksRemover.php';
require_once 'ShortenedLinksGetter.php';
require_once 'MailSender.php';

class API
{
    private ?string $authToken;
    private bool $answered = false;

    public function __construct()
    {
        if (isset($_SESSION['authToken']))
            $this->authToken = $_SESSION['authToken'];
        else
            $this->authToken = $this->generateNewAuthToken();
    }

    public function __destruct()
    {
        $_SESSION['authToken'] = $this->authToken;
    }

    public function login(?string $password): void
    {
        if (Authenticator::isPasswordCorrect($password))
            $this->sendResponse(200, 'Authorised', $this->authToken);
        else
            $this->sendResponse(401, 'Not authorised', "The provided password is incorrect");
    }

    public function logout(): void
    {
        $_SESSION = array();
        session_destroy();

        $loginPage = PageConfiguration::LOGIN_PAGE;
        header("location: $loginPage");
        exit();
    }

    public function isUserSignedIn(?string $authToken): void
    {
        if ($authToken == $this->authToken)
            $this->sendResponse(200, 'Authenticated', true);
        else
            $this->sendResponse(401, 'Unauthenticated', false);
    }

    public function addLink(?string $authToken, ?string $source, ?string $destination): void
    {
        $adder = new ShortenedLinksAdder;
        if ($authToken == $this->authToken) {
            try {
                $adder->createLink($source, $destination);
                $this->sendResponse(201, 'Link added', '');
            }  catch (DatabaseRefusesToAddLinkException $e) {
                Logger::report($e);
                $this->sendResponse(500, 'Link not added', 'Internal database error occured.');
            } catch (MissingParameterException $e){
                Logger::report($e);
                $this->sendResponse(400, 'Link not added', $e->getMessage());
            } catch (Exception $e){
                Logger::report($e);
                $this->sendResponse(500, 'Link not added', 'Unknown internal error.');
            }
        } else {
            $this->sendResponse(401, 'Not authorised', 'The provided token is incorrect.');
        }
    }

    public function removeLink(?string $token, ?string $source): void
    {
        if ($token == $this->authToken) {
            $remover = new ShortenedLinksRemover;

            if (empty($source)) {
                $this->sendResponse(400, 'Missing parameter: source', '');
            } else {
                if ($remover->remove($source))
                    $this->sendResponse(200, 'Link removed', '');
                else
                    $this->sendResponse(500, 'Link not removed due to a server error', '');
            }
        } else {
            $this->sendResponse(401, 'Not authorised', 'The provided token is incorrect.');
        }
    }

    public function getLink(?string $token, ?string $id): void
    {
        if ($token == $this->authToken) {
            $retriever = new ShortenedLinksGetter();

            if (empty($id))
                $this->sendResponse(200, 'Sent all links', $retriever->getAll());
            else
                $this->sendResponse(200, 'Sent the requested link', $retriever->get($id));
        } else {
            $this->sendResponse(401, 'Not authorised', 'The provided token is incorrect.');
        }
    }

    public function sendMail(?string $sender, ?string $subject, ?string $content): void
    {
        $mailSender = new MailSender;
        if ($mailSender->sendMail($sender, $subject, $content))
            $this->sendResponse(200, 'Mail sent', 'The mail has been successfully sent.');
        else
            $this->sendResponse(500, 'Mail not sent', 'The mail has NOT been sent due to either provding the incorrect data or internal server error.');
    }

    public function reportUnrecognisedRequestMethod(): void
    {
        $this->sendResponse(404, 'Unrecognised request method', 'The used request method is NOT proper in regard to the chosen endpoint.');
    }

    public function reportUnrecognisedEndpoint(): void
    {
        $this->sendResponse(404, 'Unrecognised endpoint', 'The requested endpoint does NOT exist.');
    }

    public function isResponseSent(): bool
    {
        return $this->answered;
    }

    private function generateNewAuthToken(): string
    {
        $token = openssl_random_pseudo_bytes(32);
        return bin2hex($token);
    }

    private function sendResponse(int $code, string $message, $data): void
    {
        header("Access-Control-Allow-Origin: " . PageConfiguration::FRONTEND_URL);
        header("Access-Control-Allow-Credentials: true");
        header("Content-Type:application/json");
        header("HTTP/1.1 " . $code);

        $response['code'] = $code;
        $response['message'] = $message;
        $response['data'] = $data;

        if ($this->answered === false)
            echo json_encode($response);

        $this->answered = true;
    }
}
