<?php
namespace TechDivision\Form\Encryption\Encrypter;

use Neos\Flow\Annotations as Flow;
use TechDivision\Form\Encryption\Exceptions\EncryptionException;

/**
 *
 * Mail Encrypter Service
 *
 * @Flow\Scope("singleton")
 */
class GpgEncrypter {

    /**
     * @Flow\InjectConfiguration(path="gpg.options")
     */
    protected $gpgOptions;

    /**
     * @param string $message
     * @return string
     * @throws EncryptionException
     */
    public function encryptMessage(string $message) {

        $message = escapeshellarg($message);

        $gpgArguments = [];
        foreach ($this->gpgOptions['gpgArguments'] as $k => $v) {
            array_push($gpgArguments, $k . ' ' . ($v===null ? '' : escapeshellarg($v)));
        }
        $gpgArguments = implode(" ", $gpgArguments);

        // encrypt message
        $command = 'echo ' . $message . ' | ' . $this->gpgOptions['gpgBinary'] . ' ' . $gpgArguments;

        $encMessage = exec($command, $output, $return);

        if ($return > 0) {
            // we do not want to give details here about what exactly failed
            throw new EncryptionException('Encryption failed. Please contact the site administrator');
        }

        $output = implode("\r\n", $output);

        return $output;
    }
}