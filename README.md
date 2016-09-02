# facebookmessenger

Facebook Serivisinde bug bulunmaktadır..Aynı mesajı 2 tane atıyor bazen ondan dolayı flood engellemek için.Cache sistemi oluşturuldu ki.. aynı gelen parametrelerden diğerini override edebilelim..



  $mind = $newBook->seq(); //mesaj içindeki seq değeri
    $senderId = $newBook->getSender();  //göndericinin sender id değeri

    if(Cache::has($senderId."_".$mind)) //eğer aynı seq id ve sender Id var ise sistemi durdur..
    {
        $newBook->logs(['cache takildi.' . $mind]);
        die();
    }
    else
    {
        Cache::set($senderId.'_'.$mind,1,5);
    }
