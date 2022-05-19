<?php

use Illuminate\Http\Request;
use App\Mail\SendMailable;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//################################################################################
//Get Tabatas Categories
Route::post('/get-categories', function (Request $request) {
    $sql = "SELECT  *
			FROM    cs_tabata_category
			order by order_number asc";
    $categories = DB::select($sql);

    return $categories; 
    
});

//Register route
Route::post('/register', function (Request $request) {
	$email = $request->email;
	$password = $request->password;
	$name = $request->name;
	$phone = $request->phone;
	$zip = $request->zip;
	$iam = $request->iam;
	$goal = $request->goal;

	$exist = DB::select('select * from compile_app_users where email = ?', [$email]);
	$dataArray = [
		'error' => 1,
    	'message' => 'Email already registered!'
	];

	//Si existe el usuario
	if(!$exist):
		$secret = Crypt::encryptString($password);
		$dataArray = [
			'email' => $email
		];
		if(!empty($password)) $dataArray['password'] = $secret;
		if(!empty($name)) $dataArray['name'] = $name;
		if(!empty($zip)) $dataArray['zip'] = $zip;
		if(!empty($phone)) $dataArray['phone'] = $phone;
		if(!empty($iam)) $dataArray['cs_iam_id'] = $iam;
		if(!empty($goal)) $dataArray['cs_goal_id'] = $goal;

		$id = DB::table('compile_app_users')->insertGetId($dataArray);
		$dataArray = [
			'error' => 0,
			'id' => $id,
			'name' => $name
		];
    endif;
	
	return $dataArray;
	
});

//Login route
Route::post('/login', function (Request $request) {
	$email = $request->email;
	$password = $request->password;

	$exist = DB::select('select * from cs_app_user where email = ?', [$email]);

	//Si existe el usuario
	if($exist):		
		if(Hash::check($password, $exist[0]->password)):
			$dataArray = [
				'error' => 0,
		    	'id' => $exist[0]->id,
		    	'name' => $exist[0]->name
			];
		else:
			$dataArray = [
				'error' => 1,
		    	'message' => 'Usuario y/o correo inválido.'
			];
		endif;
	else:
		$dataArray = [
			'error' => 1,
			'message' => 'Usuario y/o correo inválido.'
		];
    endif;
	
	return $dataArray;
	
});
// Get user info
Route::post('/get-info', function (Request $request) {
	$id = $request->id;
	$deviceID = $request->deviceID;

	$exist = DB::select('select id,email,name,zip,phone,cs_iam_id iam,cs_goal_id goal,top_streak from compile_app_users where id = ?', [$id]);
	
	$daily = DB::select('select date from user_daily where uid = ?', [$id]);	
	// var_dump($daily);
	
	// $exist[0]->daily = $daily;

	$exist[0]->completed_tabatas = DB::select('select * from cs_tabata_completed where uid = ?', [$id]);	

	if(!empty($deviceID)):
        $sql = "update  compile_app_users 
				set     deviceID='".$deviceID."',
						updated_at = '".date("Y-m-d H:i:s")."'
                where   id=".$id;
        DB::update($sql);
    endif;
	
	return $exist;
});
Route::post('/crear-operacion', function (Request $request) {
	$dataArray = [
		'name' => $request->name,
		'cs_type_id' => $request->tipo_operacion,
		'project_number' => $request->numero,
		'sign_date' => $request->fecha_firma,
		'approve_date' => $request->fecha_aprobacion,
		'elect_date' => $request->fecha_elegibilidad,
		'ammount' => $request->monto,
		'executor' => $request->ejecutor
	];

	$id = DB::table('cs_process')->insertGetId($dataArray);
	
	
	return $id;
});

//################################################################################
//Get Process list
Route::post('/get-processes', function (Request $request) {
    $sql = "SELECT 
					p.id,
					p.name,
					p.project_number,
					p.executor,
					t.type
			FROM 	cs_process p,
					cs_type t
			WHERE 	p.cs_type_id=t.id";
    $processes = DB::select($sql);

    return $processes; 
    
});
//################################################################################
//Get Process Type
Route::post('/get-process-type', function (Request $request) {
    $sql = "SELECT 	id,
					type 
			FROM 	cs_type";
    $type = DB::select($sql);

    return $type; 
    
});
//################################################################################
//Upload Files
Route::post('/upload-documents', function (Request $request) {
	$pod = $request->pod;

	$path = $request->file('pod')->store('documentos');

	// $destinationPath = public_path("uploads");
	// $path_db = 'uploads';
	// echo $destinationPath;
	// $pod->move($destinationPath,$pod->getClientOriginalName());
	
	return $pod;
});