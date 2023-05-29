<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendee;
use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Excel;
use App\Exports\TicketsExport;
use PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Uuid;
use Carbon\Carbon;
use File;
use Log;
use Exception;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $results = Ticket::all();
        $sdh_lunas = Ticket::all()->where('transaction_status', '=', 'Sukses');
        $blm_lunas = Ticket::all()->where('transaction_status', '!=', 'Sukses');
        $uang_lunas = Ticket::where('transaction_status', '=', 'Sukses')->sum('gross_amount');
        $uang_blm = Ticket::where('transaction_status', '!=', 'Sukses')->sum('gross_amount');
        $jum_sdh = $sdh_lunas->count();
        $jum_blm = $blm_lunas->count();
        $jum_tx = $results->count();
        return view('admin.tiket.index', compact('results', 'jum_tx', 'jum_sdh', 'uang_lunas', 'uang_blm', 'jum_blm'));
    }

    public function lunas_manual()
    {
        // $results = Ticket::all();
        $results = Ticket::where('transaction_status', '=', 'Sukses')->orWhere('transaction_status', '=', 'Sukses - Manual')->get();
        // return dd($results);
        return view('admin.sidebar.lunas_manual', compact('results'));
    }
    public function data_kehadiran()
    {
        // $results = Ticket::all();
        $results = Ticket::where('transaction_status', '=', 'Sukses')->orWhere('transaction_status', '=', 'Sukses - Manual')->get();
        // return dd($results);
        return view('admin.sidebar.data_kehadiran', compact('results'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $ticket = Ticket::findOrFail($id);
        // return dd($ticket);
        return view('admin.tiket.detail', compact('ticket'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }
    public function resendwa(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $url_chat = 'https://apiikaubaya.waviro.com/api/sendwa';
        $url_media = 'https://apiikaubaya.waviro.com/api/sendmedia';
        $id_trx = $request->id;
        $ticket = Ticket::find($id_trx);
        try {
            $qrcode = base64_encode(QrCode::format('svg')->size(150)->errorCorrection('H')->generate($id_trx));

            $data["name"] = $ticket->nama_lengkap;
            $data["nomer"] = $id_trx;
            $data['qr'] = $qrcode;

            $customPaper = array(0,0,1080,2043.48);
            $pdf = PDF::loadview('pdf.tiket', $data);
            $pdf->setPaper($customPaper);

            $directory_path = public_path('public/pdf');
            $secretKey = 'NJpWs4gWb9vi5Q6hMJPV';
            $nohp = Str::replaceFirst('0', '62', $ticket->no_hp);

            if(!File::exists($directory_path)) {

                File::makeDirectory($directory_path, $mode = 0755, true, true);
            }
            $filename="Ticket-$id_trx.pdf";
            $pdf->save(''.$directory_path.'/'.$filename);
            $fileurl = url("/public/public/pdf/$filename");

            // $requestChat = [
            //     'nohp' => $nohp,
            //     'pesan' => "Halo Sahabat IKA Ubaya 🙌🏻!\n\nTerimakasih kami ucapkan atas partisipasinya dalam\n*REUNI AKBAR IKA UBAYA 2023*\n\nUntuk itu, kami bermaksud mengirimkan E-PASS sebagai bukti partisipasi saudara dan dapat ditunjukkan saat registrasi acara.\n \n🤫 E-PASS di atas bersifat rahasia dan hanya berlaku untuk 1x registrasi saja, tunjukkan E-PASS di meja registrasi.\n \nOpen Registrasi  : 17:00 WIB \n\nJangan lupa untuk hadir dalam rangkaian acara pada 3 Juni 2023.\n \n#reuniakbarubaya2023\n#StrongerTogether"
            // ];
            $requestChat = '{"nohp":'.$nohp.',"pesan":"Halo Sahabat IKA Ubaya 🙌🏻!\n\nTerimakasih kami ucapkan atas partisipasinya dalam\n*REUNI AKBAR IKA UBAYA 2023*\n\nUntuk itu, kami bermaksud mengirimkan E-PASS sebagai bukti partisipasi saudara dan dapat ditunjukkan saat registrasi acara.\n \n🤫 E-PASS di atas bersifat rahasia dan hanya berlaku untuk 1x registrasi saja, tunjukkan E-PASS di meja registrasi.\n \nOpen Registrasi  : 17:00 WIB \n\nJangan lupa untuk hadir dalam rangkaian acara pada 3 Juni 2023.\n \n#reuniakbarubaya2023\n#StrongerTogether"}';
            Log::info("GM - Request Chat : ".$requestChat);
            $requestMedia = '{"nohp":'.$nohp.',"pesan": "","mediaurl": '.$fileurl.'}';
            Log::info("GM - Request Media : ".$requestMedia);

            $responseChat = $client->post($url_chat, [
                'body' => $requestChat,
                'headers' => [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'secretkey' => $secretKey
                ]
            ], ['http_errors' => false]);
            Log::info("GM - Response Chat : ".($responseChat->getBody()));

            $responseMedia = $client->post($url_media, [
                'body' => $requestMedia,
                'headers' => [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'secretkey' => $secretKey
                ]
            ], ['http_errors' => false]);
            Log::info("GM - Response Media : ".($responseMedia->getBody()));

            $obj_response_chat = json_decode($responseChat->getBody());
            $obj_response_media = json_decode($responseMedia->getBody());
            $status = false;
            if ($obj_response_chat->success == true && $obj_response_media->success == true) {
                $status = true;
            }
            return response()->json(array(
                'status'=>'oke',
                'msg'=>view('admin.tiket.resendwaDetail',compact('status'))->render()
            ),200);

        } catch (\Exception $e) {
            $status = false;
            // $response = $e->getResponse();
            // $errMsg = $response->getBody()->getContents();
            // dd($e->getResponse()->getBody()->getContents());

            $errMsg = $e->getMessage();
            Log::info("ERROR : ".$e->getMessage());
            return response()->json(array(
                'status'=>'failed',
                'reason'=> $errMsg,
                'msg'=>view('admin.tiket.resendwaDetail',compact('status','errMsg'))->render()
            ),200);
        }

    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function exportTicket()
    {
        $data = Ticket::where('transaction_status', 'Sukses')->orWhere('transaction_status', 'Sukses - Manual')->get();
        // dd($data);
        $date_now = date('Y-m-d');
        $nama_file = 'Rekap Peserta - '.$date_now.'.xlsx';

        return Excel::download(new TicketsExport($data),$nama_file);
    }
}
