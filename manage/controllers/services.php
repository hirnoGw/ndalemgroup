<?php
class Services extends CI_Controller {
    private $menu ;
    private $userkey = "hctk4m";
    private $passkey = "muach";
    // private $telepon = "081329302424";
    private $telepon = "085743901609";
    private $pesan = "bolehlah dicoba sekali lagi";
    
    /*
    skema proses script smsnya : 
    1. buat script yg dapat execute otomatis tiap harinya yg akan mengecek tanggal dari data web service.
    2. dalam script, pertama, load data konsumen beserta jatuh temponya dengan mengakses web service dari url fungsi di atas
    3. baca data konsumen dengan perulangan dan pengecekan tanggalnya.
    4. jika tanggal sesuai, execute fungsi sent_sms pada konsumen tersebut dengan format sms yg ditentukan.
    */

    public function __construct()
       {
            parent::__construct();
            $this->load->helper(array('url','form','html'));
            $this->load->library(array('session','form_validation'));
            $this->load->model(array('Operasional_mod','Laporan_mod','Biayahpp_mod'));            
       }
       
    public function index()
    {    
        echo "testing testing";
        exit();
    }

    /**
    * @since    2015
    * @task     1
    * @usedfor  hasilnya berupa data piutang konsumen beserta jatuh tempo pembayarannya
    */    
    // contoh ==> http://localhost:8080/ndalemgroup/services/piutang_konsumen
    function piutang_konsumen($idppjb=null)
    {
        $query = $this->db->query("
            SELECT
                pp.`idppjb`,
                pemesan,
                namasertifikat,
                hargasepakat,
                hp,
                carabayar,
                pp.tanggal,
                administrasi,
                pimpinan
            FROM `pembayaran_ppjb` p 
            LEFT JOIN `ppjb` pp ON p.`idppjb` = pp.`idppjb`
            LEFT JOIN `data_perumahan` dp ON pp.`idperum` = dp.`idperum`
            LEFT JOIN `data_kavling` dk ON pp.`idkavling`= dk.`idkavling`
            WHERE pp.`status` =  'dom'
            AND pp.`pimpinan` != 'menunggu'
            GROUP BY pp.`idppjb`
        ");
        $konsumen = $query->result_array();
        if (!empty($konsumen)) {
            $konsumen[$key]['data_piutang'] = array();
            foreach ($konsumen as $key => $value) {
                $id = $value["idppjb"];
                $query2 = $this->db->query("
                    SELECT 
                        p.`idbayar`,
                        p.`lunas`,
                        p.`jumlah`,
                        p.`tanggal`,
                        p.`jenisbayar`
                    FROM `pembayaran_ppjb` p
                    LEFT JOIN `ppjb` pp ON p.`idppjb`=pp.`idppjb`
                    WHERE p.`idppjb` = '$id' 
                    AND p.`lunas` <> 'lunas'
                    GROUP BY p.`tanggal`
                ");
                $konsumen[$key]['data_piutang'] = $query2->result_array();
            }
            
        }

        /*$this->db->join('ppjb','pembayaran_ppjb.idppjb=ppjb.idppjb');
        $this->db->where('pembayaran_ppjb.idppjb',$idppjb);
        $this->db->group_by('pembayaran_ppjb.tanggal');
        $data['te']=$this->db->get('pembayaran_ppjb')->result_array();
        $ret = json_encode($data['te']);*/

        echo json_encode($konsumen);
        die();
    } 



    /*fungsi di bawah ini untuk task 3
    pada task 3 terdapat 3 jenis yakni piutang borongan kavling, piutang borongan fasilitas umum
    dan piutang borongan lain-lain*/

    /**
    * @since    2015
    * @task     3
    * @usedfor  hasilnya berupa data piutang terhadap pemborong tertentu pada jenis kavling beserta jatuh tempo pembayarannya
    */  
    // contoh url ==> http://localhost:8080/ndalemgroup/services/piutang_borongan_kavling/001
    function piutang_borongan_kavling($idkbk=null)
    {
        $this->db->join('kbk','pembayaran_kbk.idkbk=kbk.idkbk');
        $this->db->join('ppjb','kbk.idppjb=ppjb.idppjb');
        $this->db->join('data_kavling','data_kavling.idkavling = kbk.idkavling','left');
        $this->db->join('data_perumahan','data_perumahan.idperum = data_kavling.idperum','left');
        $this->db->join('adm_project','adm_project.idproject = data_perumahan.idproject','left');
        $this->db->join('data_kota','data_kota.idkota = adm_project.idkota','left');
        $this->db->order_by('pembayaran_kbk.target','ASC');
        $this->db->where('kbk.idkbk',$idkbk);
        $this->db->where('pembayaran_kbk.status',"Hutang");

        $gut= $this->db->get('pembayaran_kbk')->result_array();
        // echo '<pre>'; echo $this->db->last_query(); exit();
        $ret = json_encode($gut);

        echo $ret;
        die();
    }  
    

    /**
    * @since    2015
    * @task     3
    * @usedfor  hasilnya berupa data piutang terhadap pemborong tertentu pada jenis fasilitas umum beserta jatuh tempo pembayarannya
    */ 
    // contoh url ==> http://localhost:8080/ndalemgroup/services/piutang_borongan_falum/001
    function piutang_borongan_falum($idkbf=null)
    {
        $this->db->join('kbf','pembayaran_kbf.idkbf=kbf.idkbf');
        $this->db->join('ppjb','kbf.idppjb=ppjb.idppjb');
        $this->db->join('data_kavling','data_kavling.idkavling = kbf.idkavling','left');
        $this->db->join('data_perumahan','data_perumahan.idperum = data_kavling.idperum','left');
        $this->db->join('adm_project','adm_project.idproject = data_perumahan.idproject','left');
        $this->db->join('data_kota','data_kota.idkota = adm_project.idkota','left');
        $this->db->where('kbf.idkbf',$idkbf);
        // $this->db->where('status',"Hutang");
        $this->db->order_by('pembayaran_kbf.target','ASC');
        $gut= $this->db->get('pembayaran_kbf')->result_array();
        // echo '<pre>'; echo $this->db->last_query(); exit();
        $ret = json_encode($gut);

        echo $ret;
        die();
    } 


    /**
    * @since    2015
    * @task     3
    * @usedfor  hasilnya berupa data piutang terhadap pemborong tertentu pada jenis lainnya beserta jatuh tempo pembayarannya
    */ 
    // contoh url ==> http://localhost:8080/ndalemgroup/services/piutang_borongan_lain/2
    function piutang_borongan_lain($id_hutang_lain=null)
    {
        // $this->db->select('id_bayar, tgl_rencana, jumlah');
        $this->db->join('hutang_lain','pembayaran_hutang_lain.id_hutang_lain=hutang_lain.id_hutang_lain');
        $this->db->join('data_perumahan','data_perumahan.idperum = hutang_lain.id_perum','left');
        $this->db->join('adm_project','adm_project.idproject = data_perumahan.idproject','left');
        $this->db->join('data_kota','data_kota.idkota = adm_project.idkota','left');
        $this->db->where('hutang_lain.id_hutang_lain',$id_hutang_lain);
        $this->db->where('status',"Hutang");

        $gut= $this->db->get('pembayaran_hutang_lain')->result_array();
        // echo '<pre>'; echo $this->db->last_query(); exit();
        $ret = json_encode($gut);

        echo $ret;
        die();
    }

    //cara 1
    function sent_sms()
    {
        $url = "https://reguler.zenziva.net/apps/smsapi.php?userkey=$this->userkey&passkey=$this->passkey&nohp=$this->telepon&pesan=$this->pesan";
        $test = file_get_contents($url);
        echo ($test);
        //header('Location: '.$url);
    }

    //cara 2| recomended!
    function sent_sms2()
    {
        $url = "https://reguler.zenziva.net/apps/smsapi.php";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'userkey='.$this->userkey.'&passkey='.$this->passkey.'&nohp='.$this->telepon.'&pesan='.urlencode($this->pesan));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $results = curl_exec($ch);

        print_r($results);


        curl_close($ch);

    }


}
?>