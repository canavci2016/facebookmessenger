<?php
class BotsForMessenger
{
    private $yourToken = "23101993CAN"; //kendim belirledim random oalrak
    private $accessToken = ""; //sayfamızın token facebook uygulamamızdan sayfamız için alabiliriz.. (https://developers.facebook.com/apps/1653344778315542/messenger/)
    private $inComing = [];

    /**
     * BotsForMessenger constructor.
     * @param string $yourToken
     * @param string $accessToken
     */
    public function __construct($yourToken, $accessToken)
    {
        $this->yourToken = $yourToken;
        $this->accessToken = $accessToken;
    }

    /**
     * webhook doğrulaması için facebook developer consoledan bize get isteği atılır
     * o isteğe doğru bir şekilde cevap verilmesi lazımdı.r
     * 
     * @param mix[] $request Array
     * */
    public function webhookVerify(array $request)
    {
        if (isset($request['hub_verify_token']) && isset($request['hub_challenge']) && $request['hub_verify_token'] == $this->yourToken) {
            $this->logs($request);
            echo $request['hub_challenge'];
            die();
        }
    }

    /**
     *  @param mix[] $request Array
     */
    public function postData(array  $request)
    {
        if (empty($request)) {
            $this->logs(['Servisten boş data geliyor..']);
            throw  new  Exception("Servis bos data geliyor");
        }
        $this->setIncoming($request);
    }

    /**
     *  @param mix[] $request Array
     */
    private function setIncoming(array  $request)
    {
        $this->inComing = $request;
        $this->logs($this->inComing);

    }

    /**
     * @return string
     */
    public function getIncoming()
    {
        return $this->inComing;
    }

    /**
     * @return string
     */
    public function mid()
    {
        return $this->getIncoming()['entry'][0]['messaging'][0]['message']['mid'];
    }

     /**
     * @return bool|boolean
     */
    public function isMid()
    {
        if (isset($this->getIncoming()['entry'][0]['messaging'][0]['message']['mid']))
            return true;
        else
            return false;
    }

    /**
     * @return string
     */
    public function seq()
    {
        return $this->getIncoming()['entry'][0]['messaging'][0]['message']['seq'];
    }

	/**
	 * @return bool|boolean
	 */
    public function isSeq()
    {
        if (isset($this->getIncoming()['entry'][0]['messaging'][0]['message']['seq']))
            return true;
        else
            return false;
    }




    /**
    *  gelen istekleri loglayalım.İleride onemli olabilir..
    *  @param mix[] $request Array
    *  @param string $filename  
    */
    public function logs(array $request, $fileName = "get.txt")
    {
        $touch = 'touch ' . $fileName;
        system($touch);
        $myfile = fopen($fileName, "a+") or die("Unable to open file!");
        $txt = json_encode($request);
        fwrite($myfile, $txt . "\n");
        fclose($myfile);
    }

    /**
     * kullanıcıya mesaj atmayı sağlar..
     * @param string $message
     */
    public function sendTextMessage($message)
    {
        if ($this->isSender()) {
            $sender = $this->getSender();
            $response = [
                'recipient' => ['id' => $sender],
                'message' => ['text' => $message]
            ];
            $this->send($response);
        }

    }
    

    /**
     * @param string $link 
     */
   public function sendImageMessage($link=null)
    {
        $response = [
            'recipient' => ['id' => $this->getSender()],
            'message' => ['attachment' => [
                type => "image",
                payload => [
                    "url" =>is_null($link)?"https://scontent-frt3-1.xx.fbcdn.net/v/t1.0-9/10309140_289056954592861_67130641131345898_n.jpg?oh=098a664d96a90ab8ca9162f4c5bbaf7c&oe=587F1CF4":$link,
                ],
            ]],
        ];
        $this->send($response);
    }
    
    
    
    /**
     * @return bool|boolean
     */
    public function isSender()
    {
        if (isset($this->getInComing()['entry'][0]['messaging'][0]['sender']['id']))
            return true;

        return false;
    }

    /**
     * for example : 1453719607975303
     * Mesajı gonderen kişinin id değeri
     * @return string
     */
    public function getSender()
    {
        return $this->getInComing()['entry'][0]['messaging'][0]['sender']['id'];
    }

    /**
     * @return bool|boolean
     */
    private function isMessage()
    {
        if (isset($this->getInComing()['entry'][0]['messaging'][0]['message']['text']))
            return true;

        return false;
    }


    /**
     * Mesaj atan kişinin bilgilerini senderId den bulup iletebiliyoruz.
     * @return string
     */
    public function senderInform()
    {
        $userİnform = [];
        if ($this->isSender()) {
            $userİnform = file_get_contents(sprintf('https://graph.facebook.com/v2.6/%s?fields=first_name,last_name,profile_pic,locale,timezone,gender&access_token=%s', $this->getSender(), $this->accessToken));
            $userİnform = json_decode($userİnform, true);
        }
        return $userİnform;
    }

    /**
     * kullanıcının bize gonderdiği mesaj
     * @return string
     */
    public function getMessage()
    {
        return $this->getInComing()['entry'][0]['messaging'][0]['message']['text'];
    }



    /**
     * @return bool|boolean
     */
    public function isPayload()
    {
        if ($this->getInComing()['entry'][0]['messaging'][0]['postback']['payload'])
            return true;
        else
            return false;
    }

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->getInComing()['entry'][0]['messaging'][0]['postback']['payload'];
    }

    /**
     * @return bool|boolean
     */
    public function isDelivery()
    {
        if ($this->getIncoming()['entry'][0]['messaging'][0]['delivery'])
            return true;
        else
            return false;
    }

    /**
     * @return bool|boolean
     */
    public function isRead()
    {
        if ($this->getIncoming()['entry'][0]['messaging'][0]['read'])
            return true;
        else
            return false;
    }

   


	 /**
	 * Resimli mesaj ve button gondermemizi sağlıyor..
	 * @param mixed[] $data Array
	 */
    public function sendGenericMessage(array $data)
    {
        $response = [
            'recipient' => ['id' => $this->getSender()],
            'message' => ['attachment' => [
                type => "template",
                payload => [
                    template_type => "generic",
                    elements => [

                        [
                            'title' => 'xxx.com',
                            'subtitle' => 'Next-generation is virtual reality',
                            'item_url' => 'https://www3.oculus.com/en-us/rift/',
                            'image_url' => 'https://xxx.doracdn.com/anasayfa/cok_ozel_firsatlar33.jpg',
                            'buttons' => [

                                [
                                    'type' => 'web_url',
                                    "title" => "Open Web URL",
                                    "url" => "http://www.xxx.com/index.php",
                                ],

                                [
                                    'type' => 'postback',
                                    "title" => "Call Postback",
                                    "payload" => "Payload for second bubble",
                                ],
                            ]

                        ],

                        [
                            'title' => 'touch',
                            'subtitle' => 'Next-generation is virtual reality',
                            'item_url' => 'https://www3.oculus.com/en-us/rift/',
                            'image_url' => 'https://xxx.doracdn.com/anasayfa/anasayfa_degisim1.jpg',
                            'buttons' => [
                                [
                                    'type' => 'web_url',
                                    "title" => "Open Web URL",
                                    "url" => "https://www.oculus.com/en-us/rift/",
                                ],

                                [
                                    'type' => 'postback',
                                    "title" => "Call Postback",
                                    "payload" => "Payload for second bubble",
                                ],
                            ]

                        ],
                    ],
                ],
            ]],
        ];
        $response['message']['attachment']['payload']['elements'] = $data;
        $this->send($response);
    }

    /**
    * @param string $response
    */
    public function send($response)
    {
        $ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token=' . $this->accessToken);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
    }


}


?>
