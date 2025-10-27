<?php
namespace App\Service;

use Cake\Core\Configure;use Cake\Log\Log;

class VNPayService
{
    private $tmnCode;
    private $hashSecret;
    private $url;
    private $returnUrl;
    
    public function __construct()
    {
        $vnpConfig = Configure::read('VNPay');
        $this->tmnCode = $vnpConfig['tmn_code'];
        $this->hashSecret = $vnpConfig['hash_secret'];
        $this->url = $vnpConfig['url'];
        $this->returnUrl = $vnpConfig['return_url'];
    }
    
    /**
     * Create VNPay payment URL
     * 
     * @param string $orderId Order ID
     * @param int $amount Amount (VND)
     * @param string $orderInfo Order information
     * @param string $ipAddr Customer's IP address
     * @return string Payment URL
     */
    public function createPaymentUrl($orderId, $amount, $orderInfo, $ipAddr)
    {
        $vnpData = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $this->tmnCode,
            'vnp_Amount' => $amount * 100, // VNPay requires amount * 100
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => $ipAddr,
            'vnp_Locale' => 'vn',
            'vnp_OrderInfo' => $orderInfo,
            'vnp_OrderType' => 'other',
            'vnp_ReturnUrl' => $this->returnUrl,
            'vnp_TxnRef' => $orderId,
        ];
        // Sort array by key (alphabetically)
        ksort($vnpData);
        
        // Create query string
        $query = http_build_query($vnpData);
        
        // Create secure hash
        $vnpSecureHash = hash_hmac('sha512', $query, $this->hashSecret);
        
        // Final URL
        $paymentUrl = $this->url . '?' . $query . '&vnp_SecureHash=' . $vnpSecureHash;
        
        return $paymentUrl;
    }
    
    /**
     * Verify callback from VNPay
     * 
     * @param array $params Query params from VNPay
     * @return bool True if valid
     */
    public function verifyReturnUrl($params)
    {
        if (!isset($params['vnp_SecureHash'])) {
            return false;
        }
        
        $vnpSecureHash = $params['vnp_SecureHash'];
        
        $inputData = $params;
        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);
        
        // Sort data
        ksort($inputData);
        
        // Create query string
        $query = http_build_query($inputData);
        
        // Create secure hash for comparison
        $secureHash = hash_hmac('sha512', $query, $this->hashSecret);
        
        return $secureHash === $vnpSecureHash;
    }
    
    /**
     * Get error message from response code
     * 
     * @param string $responseCode Error code
     * @return string Message
     */
    public function getResponseMessage($responseCode)
    {
        $messages = [
            '00' => 'Transaction successful',
            '07' => 'Transaction is suspected (related to fraud, unusual transactions).',
            '09' => 'Transaction failed: Card/Account not registered for Internet Banking service.',
            '10' => 'Transaction failed: Incorrect card/account information entered more than 3 times',
            '11' => 'Transaction failed: Payment timeout. Please try again.',
            '12' => 'Transaction failed: Card/Account is locked.',
            '13' => 'Transaction failed: Wrong OTP. Please try again.',
            '24' => 'Transaction failed: Customer cancelled the transaction',
            '51' => 'Transaction failed: Insufficient balance.',
            '65' => 'Transaction failed: Account exceeded daily transaction limit.',
            '75' => 'Payment bank is under maintenance.',
            '79' => 'Transaction failed: Wrong password entered too many times. Please try again',
            '99' => 'Other errors (not listed in error codes)',
        ];
        
        return $messages[$responseCode] ?? 'Unknown error';
    }
}