<?php
require_once 'Logger.php';
require_once 'PageConfiguration.php';

class MailSender
{
    public function sendMail(
        ?string $sender,
        ?string $subject,
        ?string $content
        ): bool
    {
        try {
            if (!$this->validateEmail($sender))
                throw new Exception('The given e-mail address is malformed');
            if (empty($subject))
                throw new Exception('The mail\'s subject cannot be empty');
            if (empty($content))
                throw new Exception('The mail\'s content cannot be empty');

            $this->invokeMailFunction($sender, $subject, $content);
            return true;
        } catch (Exception $e){
            Logger::report($e);
            return false;
        }
    }

    private function invokeMailFunction(
        string $sender,
        string $subject,
        string $content
        ): void
    {
        $headers = $this->generateHeaders($sender);
        if (!mail(PageConfiguration::ADMIN_EMAIL, $subject, $content, $headers))
        {
            $errorArray = error_get_last();
            throw new Exception( $errorArray['message'] );
        }
    }

    private function generateHeaders(string $sender): string
    {
        $ownerEmail = PageConfiguration::ADMIN_EMAIL;
        $headers = 'MIME-Version: 1.0';
        $headers .= 'Content-type: text/html; charset=utf-8';
        $headers .= "To: In-Lenses <$ownerEmail>";
        $headers .= "From: Formularz kontaktowy <$sender>";

        return $headers;
    }

    private function validateEmail(?string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validatePhoneNumber(?string $phoneNumber): bool
    {
        if ($phoneNumber === null)
            return false;

        $phoneNumber = str_replace(' ', '', $phoneNumber);
        $phoneNumber = str_replace('-', '', $phoneNumber);
        return is_numeric($phoneNumber);
    }

}
