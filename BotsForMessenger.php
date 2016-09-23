<?php


trait Account
{

    /*
    * resimli mesaj ve button gondermemizi sağlıyor..
    * */
    public function linkAccount(array $data)
    {
        $response = [
            'recipient' => ['id' => $this->getSender()],
            'message' => ['attachment' => [
                "type" => "template",
                "payload" => [
                    "template_type" => "generic",
                    "elements" => [

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
                                ]
                            ]

                        ],

                    ],
                ],
            ]],
        ];
        $response['message']['attachment']['payload']['elements'] = $data;
        $this->send($response);
    }


}


class BotsForMessenger
{
    use Account;
    private $yourToken = "KENDI BELIRLEDIGIN TOKEN (KISIYE BAGLI MAKSAT SERVIS ILE ILETISIM)"; //kendim belirledim random oalrak
    private $accessToken = "";//sayfamızın token facebook uygulamamızdan sayfamız için alabiliriz.. (https://developers.facebook.com/apps/1653344778315542/messenger/)
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

    /*
     *
     * webhook doğrulaması için facebook developer consoledan bize get isteği atılır
     * o isteğe doğru bir şekilde cevap verilmesi lazımdı.r
     * */
    public function webhookVerify(array $request)
    {
        if (isset($request['hub_verify_token']) && isset($request['hub_challenge']) && $request['hub_verify_token'] == $this->yourToken) {
            $this->logs($request);
            echo $request['hub_challenge'];
            die();
        }
    }

    public function postData(array  $request)
    {
        if (empty($request)) {
            $this->logs(['Servisten boş data geliyor..']);
            throw  new  Exception("Servis bos data geliyor");
        }
        $this->setIncoming($request);
    }

    private function setIncoming(array  $request)
    {
        $this->inComing = $request;
        $this->logs($this->inComing);

    }

    public function getIncoming()
    {
        return $this->inComing;
    }

    public function mid()
    {
        return $this->getIncoming()['entry'][0]['messaging'][0]['message']['mid'];
    }

    public function isMid()
    {
        if (isset($this->getIncoming()['entry'][0]['messaging'][0]['message']['mid']))
            return true;
        else
            return false;
    }


    public function seq()
    {
        return $this->getIncoming()['entry'][0]['messaging'][0]['message']['seq'];
    }

    public function isSeq()
    {
        if (isset($this->getIncoming()['entry'][0]['messaging'][0]['message']['seq']))
            return true;
        else
            return false;
    }


//gelen istekleri loglayalım.İleride onemli olabilir..
    public function logs(array $request, $fileName = "get.txt")
    {
        $touch = 'touch ' . $fileName;
        system($touch);
        $myfile = fopen($fileName, "a+") or die("Unable to open file!");
        $txt = json_encode($request);
        fwrite($myfile, $txt . "\n");
        fclose($myfile);
    }


    public function isSender()
    {
        if (isset($this->getInComing()['entry'][0]['messaging'][0]['sender']['id']))
            return true;

        return false;
    }

    /*
     * @return string
     * for example : 1453719607975303
     * Mesajı gonderen kişinin id değeri
     * */
    public function getSender()
    {
        return $this->getInComing()['entry'][0]['messaging'][0]['sender']['id'];
    }

    private function isMessage()
    {
        if (isset($this->getInComing()['entry'][0]['messaging'][0]['message']['text']))
            return true;

        return false;
    }


    /*
     *
     * mesaj atan kişinin bilgilerini senderId den bulup iletebiliyoruz.
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

    /*
     *@return string
     *
     * kullanıcının bize gonderdiği mesaj
     * */
    public function getMessage()
    {
        return $this->getInComing()['entry'][0]['messaging'][0]['message']['text'];
    }


    public function isPayload()
    {
        if ($this->getInComing()['entry'][0]['messaging'][0]['postback']['payload'])
            return true;
        else
            return false;
    }

    public function getPayload()
    {
        return $this->getInComing()['entry'][0]['messaging'][0]['postback']['payload'];
    }

    public function isDelivery()
    {
        if ($this->getIncoming()['entry'][0]['messaging'][0]['delivery'])
            return true;
        else
            return false;
    }

    public function isRead()
    {
        if ($this->getIncoming()['entry'][0]['messaging'][0]['read'])
            return true;
        else
            return false;
    }


    /*
       * kullanıcıya mesaj atmayı sağlar..
       *
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

    public function sendImageMessage($image_path = null)
    {
        $response = [
            'recipient' => ['id' => $this->getSender()],
            'message' => ['attachment' => [
                type => "image",
                payload => [
                    "url" => $image_path,
                ],
            ]],
        ];
        $this->send($response);
    }

    public function sendButtonMessage($message, array  $buttons)
    {
        $response = [
            'recipient' => ['id' => $this->getSender()],
            'message' => ['attachment' => [
                "type" => "template",
                "payload" => [
                    "template_type" => "button",
                    "text" => "what do you mean",
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
            ]],
        ];

        $response['message']['attachment']['payload']['text'] = $message;
        $response['message']['attachment']['payload']['buttons'] = $buttons;
        $this->send($response);
    }

    /*
     * resimli mesaj ve button gondermemizi sağlıyor..
     * */
    public function sendGenericMessage(array $data)
    {
        $response = [
            'recipient' => ['id' => $this->getSender()],
            'message' => ['attachment' => [
                "type" => "template",
                "payload" => [
                    "template_type" => "generic",
                    "elements" => [

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
                                [
                                    'type' => 'element_share',
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


    /*
     * satış ekranı şeklinde yapmamaızı sağlarr..
     * */
    public function sendReceiptMessage(array $data)
    {
        $response = [
            'recipient' => ['id' => $this->getSender()],
            'message' => [
                'attachment' => [
                    "type" => "template",
                    "payload" => [
                        "template_type" => "receipt",
                        "recipient_name" => "Stephane Crozatier",
                        "order_number" => "12345678902",
                        "currency" => "USD",
                        "payment_method" => "Visa 2345",
                        "order_url" => "http://petersapparel.parseapp.com/order?order_id=123456",
                        "timestamp" => "1428444852",
                        "elements" => [
                            [
                                "title" => "Classic White T-Shirt",
                                "subtitle" => "100% Soft and Luxurious Cotton",
                                "quantity" => 2,
                                "price" => 50,
                                "currency" => "USD",
                                "image_url" => "http://petersapparel.parseapp.com/img/whiteshirt.png"
                            ],
                            [
                                "title" => "Classic Gray T-Shirt",
                                "subtitle" => "100% Soft and Luxurious Cotton",
                                "quantity" => 1,
                                "price" => 25,
                                "currency" => "USD",
                                "image_url" => "http://petersapparel.parseapp.com/img/grayshirt.png"
                            ]
                        ],

                        "address" => [
                            "street_1" => "1 Hacker Way",
                            "street_2" => "",
                            "city" => "Menlo Park",
                            "postal_code" => "94025",
                            "state" => "CA",
                            "country" => "US"
                        ],
                        "summary" => [
                            "subtotal" => 75.00,
                            "shipping_cost" => 4.95,
                            "total_tax" => 6.19,
                            "total_cost" => 56.14
                        ],
                        "adjustments" => [
                            [
                                "name" => "New Customer Discount",
                                "amount" => 20
                            ],
                            [
                                "name" => "$10 Off Coupon",
                                "amount" => 10
                            ]
                        ]


                    ],
                ]
            ],
        ];

        $this->send($response);
    }

//sol kosede cıkan menu
    public function persistMenu($data)
    {
        $response = [
            'setting_type' => 'call_to_actions',
            'thread_state' => 'existing_thread',
            'call_to_actions' => [
                [
                    "type" => "postback",
                    "title" => "Başlangıç",
                    "payload" => "USER_DEFINED_PAYLOAD"
                ],
                [
                    "type" => "postback",
                    "title" => "Mağazalar",
                    "payload" => "magaza-ariyorum"
                ],
                [
                    "type" => "web_url",
                    "title" => "Siteye Git",
                    "url" => "http://xxx.com/"
                ]
            ],
        ];
        $response['call_to_actions'] = $data;

        $this->threadSend($response);
    }

    public function send($response, $link = 'messages')
    {
        $ch = curl_init('https://graph.facebook.com/v2.6/me/' . $link . '?access_token=' . $this->accessToken);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);


    }

    public function threadSend($response)
    {
        $this->send($response, 'thread_settings');
    }


}





?>
