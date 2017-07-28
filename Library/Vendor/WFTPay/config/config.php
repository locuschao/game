<?php
class Config{
    private $cfg = array(
		//�ӿ������ַ���̶����䣬�����޸�
        'url'=>'https://pay.swiftpass.cn/pay/gateway',          // 'url'=>'https://pay.swiftpass.cn/pay/gateway',
		//�����̻��ţ��̻����Ϊ�Լ���
        'mchId'=>'7502000101',                                      //'mchId'=>'7552900037',
		//������Կ���̻����Ϊ�Լ���
        'key'=>'1c17cbc7806df81f88eb265890c149a4',              //'key'=>'11f4aca52cf400263fdd8faf7a69e007',
		//�汾��Ĭ��2.0
        'version'=>'2.0'
       );
    
    public function C($cfgName){
        return $this->cfg[$cfgName];
    }
}
?>