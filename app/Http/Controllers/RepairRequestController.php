<?php
namespace App\Http\Controllers;
use App\Models\{RepairRequest, Building, Device, RegisterTag, Assignment, User, Status, Evaluation};
use Illuminate\Support\Facades\{Auth, DB, Log, Http};
use Illuminate\Http\Request;
use App\Models\RegisterDevice;
use App\Models\Schedule;
use Carbon\Carbon;
class RepairRequestController extends Controller
{
    // ฟังก์ชันสำหรับแสดงรายการแจ้งซ่อมสำหรับ Admin
public function index(Request $request)
{
    try {
        $user = Auth::user();
        $status = $request->input('status'); // รับค่าจาก URL

        // Log ค่าของ status เพื่อตรวจสอบ
        Log::info('Current Status: ' . $status);

        // สร้าง Query
        $query = RepairRequest::with('status', 'device');

        // ตรวจสอบสิทธิ์ของผู้ใช้
        if ($user->role == 0) { // Admin
            // Admin: แสดงคำขอทั้งหมด
        } elseif ($user->role == 1) { // IT Staff
            // IT Staff: กรองตามการมอบหมาย
            $query->whereHas('assignments', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } else { // Regular User
            // Regular User: แสดงเฉพาะคำขอที่ผู้ใช้สร้างเอง
            $query->where('user_id', $user->id);
        }

        // ถ้ามีการระบุสถานะใน URL จะกรองตามสถานะ
        if ($status !== null) {
            $query->where('StatusID', $status);
        }

        // เรียงลำดับตามวันที่ล่าสุด
        $requests = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('repair_requests.index', compact('requests'));
    } catch (\Exception $e) {
        Log::error('Error retrieving repair requests: ' . $e->getMessage());

        // สร้างอาร์เรย์ว่างเพื่อป้องกัน undefined variable
        $requests = collect(); // หรือ $requests = []; ขึ้นอยู่กับว่าคุณต้องการใช้แบบไหน

        return view('repair_requests.index', compact('requests'))->with('error', 'There was an error retrieving the repair requests.');
    }
}


  public function dashboard()
{
    $userId = Auth::id(); // ดึง user_id ของผู้ใช้ที่ล็อกอินอยู่

    $totalRequests = RepairRequest::where('user_id', $userId)->count(); // จำนวนงานแจ้งซ่อมทั้งหมดของผู้ใช้
    $pendingRequests = RepairRequest::where('user_id', $userId)->where('StatusID', 0)->count(); // Pending
    $inProgressRequests = RepairRequest::where('user_id', $userId)->where('StatusID', 2)->count(); // กำลังดำเนินการ
    $completedRequests = RepairRequest::where('user_id', $userId)->where('StatusID', 3)->count(); // ดำเนินการแล้ว

    // ดึงรายการแจ้งซ่อมทั้งหมดของผู้ใช้และเรียงลำดับตาม StatusID ที่กำหนด
    $repairRequests = RepairRequest::where('user_id', $userId)
                        ->orderByRaw("FIELD(StatusID, 0, 2, 3)") // เรียง StatusID = 0 ด้านบน, StatusID = 2 กลาง, StatusID = 3 ด้านล่าง
                        ->orderBy('Date', 'desc') // จากใหม่ไปเก่า
                        ->get();

    // ส่งค่าตัวแปรทั้งหมดไปที่ view dashboard
    return view('dashboard', compact(
        'totalRequests',
        'pendingRequests',
        'inProgressRequests',
        'completedRequests',
        'repairRequests' // เพิ่มตัวแปรนี้เพื่อส่งข้อมูลการแจ้งซ่อมไปยังหน้า dashboard
    ));
}


    // ฟังก์ชันสำหรับแสดง Dashboard ของ Admin
  public function adminDashboard()
{
    try {
        $user = Auth::user();
        
        // ตรวจสอบว่าเป็น admin
        if ($user->role == 0) { // Admin
            // เรียงตามวันที่ จากใหม่ไปเก่า
            $requests = RepairRequest::with(['status', 'device', 'assignments.user'])
                                     ->orderByRaw("FIELD(StatusID, 0, 2, 3)")
                                     ->orderBy('Date', 'desc')  // เรียงตามวันที่ จากใหม่ไปเก่า
                                     ->get();
        }

        // ส่งข้อมูลไปยังหน้า admin.blade.php
        return view('admin', compact('requests'));

    } catch (\Exception $e) {
        Log::error('Error retrieving repair requests: ' . $e->getMessage());
        return view('admin')->with('error', 'There was an error retrieving the repair requests.');
    }
}

    // ฟังก์ชันสำหรับ Admin มอบหมายงาน
    public function assign($ticketId)
{
    $request = RepairRequest::where('TicketID', $ticketId)->firstOrFail(); // ดึงข้อมูลการแจ้งซ่อมจาก TicketID
    $itUsers = User::where('role', 1)->get(); // ดึงรายชื่อ IT ทั้งหมด
    return view('repair_requests.assign', compact('request', 'itUsers'));
}

    // ฟังก์ชันสำหรับบันทึกการมอบหมายงาน
 public function storeAssignment(Request $request, $id)
{
    $validatedData = $request->validate([
        'user_id' => 'required|exists:users,id',
    ]);

    // สร้างการมอบหมายงานใหม่
    Assignment::create([
        'TicketID' => $id,
        'user_id' => $validatedData['user_id'],
    ]);

       $repairRequest = RepairRequest::where('TicketID', $id)->firstOrFail();
    $assignedUser = User::findOrFail($validatedData['user_id']); // ดึงข้อมูล IT ที่ได้รับมอบหมาย

    // ส่งการแจ้งเตือนให้ IT ที่ได้รับมอบหมาย
    $message = "คุณได้รับมอบหมายงานหมายเลข TicketID: " . $repairRequest->TicketNumber;
    $this->sendLineNotify($message, $assignedUser->line_token); // สมมติว่าคุณเก็บ Line Token ของผู้ใช้แต่ละคนในฐานข้อมูล

    // แจ้งเตือนผู้ใช้ที่สร้างคำร้อง
    $user = User::find($repairRequest->user_id);
    if ($user) {
        $userMessage = "คำร้องซ่อมของคุณหมายเลข TicketID: " . $repairRequest->TicketNumber . " ได้รับมอบหมายให้กับ IT Staff: " . $assignedUser->name;
        $this->sendLineNotify($userMessage, $user->line_token); // ส่งการแจ้งเตือนให้กับผู้ใช้
    }

    // แจ้งเตือนผู้ดูแลระบบ
    $admins = User::where('role', 0)->get(); // ดึงผู้ดูแลระบบทั้งหมด
    foreach ($admins as $admin) {
        $adminMessage = "คำร้องซ่อมหมายเลข TicketID: " . $repairRequest->TicketNumber . " ได้รับมอบหมายให้กับ IT Staff: " . $assignedUser->name;
        $this->sendLineNotify($adminMessage, $admin->line_token); // ส่งการแจ้งเตือนให้ผู้ดูแลระบบ
    }

    return redirect()->route('admin')->with('success', 'Repair Request Assigned Successfully');
}
public function getDeviceDetails(Request $request)
{
    // Fetch device details by EquipmentNumber
    $device = DB::table('register_device')
                ->where('EquipmentNumber', $request->input('EquipmentNumber'))
                ->first();

    if ($device) {
        return response()->json([
            'DeviceName' => $device->Brand . ' ' . $device->Model,
            'location' => $device->location,
            'DeviceID' => $device->DeviceID,
        ]);
    }

    return response()->json(['error' => 'Device not found'], 404);
}
public function getEquipmentNumber(Request $request)
{
    $tagNumber = $request->input('TagNumber');
    $repairRequest = RepairRequest::where('TagNumber', $tagNumber)->first();

    if ($repairRequest) {
        return response()->json([
            'EquipmentNumber' => $repairRequest->EquipmentNumber,
            'DeviceID' => $repairRequest->Device_ID, // ถ้าต้องการดึง Device_ID
            
        ]);
    }

    return response()->json(['error' => 'ไม่พบข้อมูล']);
}
 public function getTagNumber(Request $request)
{
    $equipmentNumber = $request->input('EquipmentNumber');

    // ค้นหาข้อมูลตามหมายเลขอุปกรณ์
    $repairRequest = RepairRequest::where('EquipmentNumber', $equipmentNumber)->first();

    if ($repairRequest) {
        return response()->json([
            'TagNumber' => $repairRequest->TagNumber, // คืนค่า TagNumber
        ]);
    }

    return response()->json(['error' => 'ไม่พบข้อมูล']);
}


    // ฟังก์ชันสำหรับแสดงรายการแจ้งซ่อมสำหรับ IT
public function itIndex()
{
    $requests = RepairRequest::whereHas('assignments', function ($query) {
        $query->where('user_id', Auth::id());
    })
    ->with('status', 'device')
    ->orderByRaw("FIELD(StatusID, 0, 2, 3)") // เรียง StatusID = 0 ด้านบน, StatusID = 2 กลาง, StatusID = 3 ด้านล่าง
    ->orderBy('Date', 'desc')  // เรียงตามวันที่ จากใหม่ไปเก่า
    ->get();

    return view('it', compact('requests'));
}
  
private function getLineAccessToken($code)
{
    Log::info('Received code: ' . $code); // เพิ่มการบันทึก code ที่ได้รับ

    $response = Http::asForm()->post('https://notify-bot.line.me/oauth/token', [
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => 'https://deepskyblue-alpaca-650995.hostingersite.com/auth/line/callback',
        'client_id' => '9M8Z8VuVUZbHfxGK3A6vZ5',
        'client_secret' => 'VwPwq670Z5iQ6BUVjjShJcpp7WwC5KJPqFrhboWB66X',
    ]);

    if ($response->successful()) {
        $accessToken = $response->json()['access_token'];
        Log::info('Access Token: ' . $accessToken);

        $user = User::find(Auth::id());
        if ($user) {
            $user->line_token = $accessToken; // บันทึก token
            $user->save();
            Log::info('Line token saved for user ID: ' . $user->id);
        } else {
            Log::error('User not found for Auth ID: ' . Auth::id());
        }

        return redirect()->route('dashboard')->with('success', 'LINE Notify authorized successfully.');
    } else {
        Log::error('Failed to get access token: ' . $response->body());
        return redirect()->route('dashboard')->with('error', 'Failed to authorize LINE Notify.');
    }
}



     private function sendNotification(RepairRequest $repairRequest, $type)
    {
        $message = match($type) {
            'assigned' => "IT has been assigned the task TicketID: " . $repairRequest->TicketNumber,
            'status_changed' => "The status of TicketID: " . $repairRequest->TicketNumber . " has changed.",
            default => "Notification for TicketID: " . $repairRequest->TicketNumber
        };

        $this->sendLineNotify($message);

        // Notify the user who created the request
        $user = User::find($repairRequest->user_id);
        if ($user) {
            $userMessage = $message; // Adjust the message if needed
            $this->sendLineNotify($userMessage);
        }

        // Notify all admins
        $admins = User::where('role', 0)->get();
        foreach ($admins as $admin) {
            $adminMessage = $message; // Adjust the message if needed
            $this->sendLineNotify($adminMessage);
        }
    }

private function sendLineNotify($message, $token = null)
{
    $token = $token ?? Auth::user()->line_token; // ใช้ token ของผู้ใช้
    if (!$token) {
        Log::error('No LINE token available for user ID: ' . Auth::id());
        return;
    }

    $response = Http::withHeaders(['Authorization' => 'Bearer ' . $token])
        ->asForm()
        ->post('https://notify-api.line.me/api/notify', ['message' => $message]);

    if ($response->successful()) {
        Log::info('LINE Notify sent successfully.');
    } else {
        Log::error('Error sending LINE Notify: ' . $response->body());
        }
    }

    
    
    
    public function userIndex()
    {
        $requests = RepairRequest::where('user_id', Auth::id())->with('status', 'device')->get();
        return view('dashboard', compact('requests'));
    }

    // ฟังก์ชันสำหรับสร้างรายการแจ้งซ่อมใหม่
    public function create(Request $request)
    {
        // Get the tag number from the input
        $tagNumber = $request->input('TagNumber');
        
        // Get user_id of the logged-in user
        $userId = Auth::id(); 
    
        // Get tag numbers that the user has registered
        $tagNumbers = DB::table('register_tags as rt')
            ->join('repair_requests as rr', 'rt.repair_request_id', '=', 'rr.TicketID')
            ->where('rr.user_id', $userId)
            ->pluck('rt.TagNumber')
            ->toArray(); 
        
        // Get device information based on the selected tag number
        $deviceInfo = null;
        if ($tagNumber) {
            $repairRequest = RepairRequest::where('TagNumber', $tagNumber)->with('device')->first();
            if ($repairRequest) {
                $deviceInfo = $repairRequest->device; // Get device information
            }
        }
    
        // Fetch all devices for the dropdown
        $devices = Device::all(); 
    
        // Send data to view
        return view('repair_requests.create', [
            'devices' => $devices,
            'tagNumbers' => $tagNumbers,
            'tagNumber' => $tagNumber,
            'deviceInfo' => $deviceInfo, // Pass device information to the view
        ]);
    }
    // ใน RepairRequestController.php

  public function getTagInfo(Request $request)
{
    $tagNumber = $request->input('tagNumber');
    Log::info('getTagInfo called with TagNumber: ' . $tagNumber);

    // ตรวจสอบว่ามี TagNumber หรือไม่
    if (!$tagNumber) {
        Log::error('No TagNumber provided');
        return response()->json(['error' => 'Tag number is required'], 400);
    }

    // ค้นหาข้อมูลจาก RegisterTag ตาม TagNumber พร้อมโหลดความสัมพันธ์กับ RepairRequest และ Device
    $tagInfo = RegisterTag::where('TagNumber', $tagNumber)
        ->whereHas('repairRequest', function($q) {
            $q->whereNotNull('Device_ID');
        })
        ->with(['repairRequest', 'device'])
        ->first();

    // ตรวจสอบว่าพบ RegisterTag หรือไม่
    if ($tagInfo) {
        Log::info('RegisterTag found: ' . $tagInfo->TagNumber);
    } else {
        Log::warning('No RegisterTag found for TagNumber: ' . $tagNumber);
        return response()->json(['error' => 'No RegisterTag found'], 404);
    }

    // ตรวจสอบว่ามี RepairRequest หรือไม่
    if ($tagInfo->repairRequest) {
        Log::info('RepairRequest found: Tel=' . $tagInfo->repairRequest->Tel . ', Device_ID=' . $tagInfo->repairRequest->Device_ID);
        
        // Fetch the additional fields from the device
        $device = $tagInfo->device;

        return response()->json([
            'phone' => $tagInfo->repairRequest->Tel, // เบอร์โทรศัพท์จาก RepairRequest
            'device_id' => $tagInfo->repairRequest->Device_ID, // ID ของอุปกรณ์
            'device_name' => $device ? $device->Devicename : '', // ชื่ออุปกรณ์ หรือค่าว่างถ้าไม่มี
            'brand' => $device ? $device->Brand : '', // ยี่ห้อ
            'model' => $device ? $device->Model : '', // รุ่น
            'other_features' => $device ? $device->OtherFeatures : '', // คุณสมบัติอื่น ๆ
            'location' => $device ? $device->location : '', // สถานที่
        ]);
    }

    Log::warning('No RepairRequest found for TagNumber: ' . $tagNumber);
    return response()->json(['error' => 'No RepairRequest found'], 404);
}

    
    public function getRepairDetails(Request $request)
{
    $tagNumber = $request->input('tag_number');
    $repairRequest = RepairRequest::where('TagNumber', $tagNumber)->first(); // ดึงข้อมูลการซ่อมเก่าที่เกี่ยวข้อง

    return response()->json($repairRequest);
}
public function store(Request $request) 
{
    try {
        Log::info('Request Data: ', $request->all());

        // Validate incoming data
        $validatedData = $request->validate([
            'TagNumber' => 'nullable|string|max:30',
            'RepairDetail' => 'required|string|max:255',
            'Tel' => 'required|digits:10',
            'location' => 'required|string|max:255',
            'EquipmentNumber' => 'required|string|max:255',
        ]);

        // Set TagNumber to null if 'ไม่มี' is selected
        if ($validatedData['TagNumber'] === '') {
            $validatedData['TagNumber'] = null;
        }

        // Fetch Device ID and other details from register_device
        $device = DB::table('register_device')
            ->where('EquipmentNumber', $validatedData['EquipmentNumber'])
            ->first();

        if (!$device) {
            return back()->withErrors(['error' => 'ไม่พบอุปกรณ์นี้ในระบบ']);
        }

        // ตรวจสอบว่ามีการแจ้งซ่อมที่ยังไม่เสร็จสิ้นสำหรับ TagNumber หรือ EquipmentNumber หรือไม่
        $existingRepair = RepairRequest::where(function($query) use ($validatedData) {
                // ถ้า TagNumber เป็น null, ให้ใช้เฉพาะ EquipmentNumber เท่านั้น
                if ($validatedData['TagNumber'] === null) {
                    $query->where('EquipmentNumber', $validatedData['EquipmentNumber']);
                } else {
                    $query->where('TagNumber', $validatedData['TagNumber'])
                          ->orWhere('EquipmentNumber', $validatedData['EquipmentNumber']);
                }
            })
            ->where('StatusID', '<>', 3) // ตรวจสอบสถานะที่ไม่ใช่เสร็จสิ้น (StatusID != 3)
            ->first();

        if ($existingRepair) {
            return back()->withErrors(['error' => 'ไม่สามารถแจ้งซ่อมได้ เนื่องจากมีการแจ้งซ่อมอยู่แล้ว']);
        }

        // Create the repair request
        $repairRequest = RepairRequest::create([
            'Date' => now(),
            'TagNumber' => $validatedData['TagNumber'],
            'RepairDetail' => $validatedData['RepairDetail'],
            'Device_ID' => $device->DeviceID,
            'Tel' => $validatedData['Tel'],
            'StatusID' => Status::where('Statusname', 'Pending')->first()->StatusID,
            'user_id' => Auth::id(),
            'EquipmentNumber' => $validatedData['EquipmentNumber'],
            'location' => $device->location,
        ]);

        Log::info('Created RepairRequest with TicketNumber: ' . $repairRequest->TicketNumber);

        // Notify via LINE Notify
        $message = "ผู้ใช้ได้ทำการแจ้งซ่อม หมายเลข TicketID: " . $repairRequest->TicketNumber;
        $this->sendLineNotify($message);

        return redirect()->route('dashboard')->with('success', 'Repair request created successfully');
    } catch (\Exception $e) {
        Log::error('Error storing RepairRequest: ' . $e->getMessage());
        return back()->withErrors(['error' => $e->getMessage()]);
    }
}

public function searchByTag(Request $request)
{
    $tagNumber = $request->input('TagNumber');

    // ค้นหาข้อมูลการแจ้งซ่อมที่เกี่ยวข้องกับ TagNumber และจัดเรียงตาม StatusID
    $results = RepairRequest::where('TagNumber', $tagNumber)
        ->with(['status', 'device', 'registerTag.building']) // ดึงข้อมูล building จาก registerTag
        ->orderByRaw("FIELD(StatusID, 0, 2, 3)") // เรียงลำดับตาม StatusID: 0 อยู่บนสุด, 2 อยู่กลาง, 3 อยู่ล่างสุด
        ->orderBy('Date', 'desc') // เรียงตามวันที่จากใหม่ไปเก่า
        ->get();

    // ตรวจสอบว่ามีผลลัพธ์หรือไม่
    if ($results->isEmpty()) {
        return redirect()->route('dashboard')->with('error', 'ไม่พบข้อมูลการแจ้งซ่อมสำหรับหมายเลขแท็กนี้');
    }

    return view('repair_requests.search', compact('results', 'tagNumber'));
}


    public function showSearchForm()
    {
        return view('repair_requests.search');
    }
    public function generateTagNumber($deviceType)
    
    {
        // Fetch the last TagNumber based on the device type (e.g., N for a specific device type)
        $lastTag = RegisterTag::where('TagNumber', 'like', $deviceType . '%')
            ->orderBy('TagNumber', 'desc')
            ->first();

        // Increment the last number or start with 1 if there are no tags
        $newNumber = $lastTag ? (intval(substr($lastTag->TagNumber, strlen($deviceType))) + 1) : 1;

        return sprintf('%s%04d', $deviceType, $newNumber); // Format to N0001, N0002, etc.
    }

 public function showRegisterTagForm($repair_request_id)
{
    // ดึงข้อมูลจาก repair_request โดยใช้ repair_request_id
    $repairRequest = RepairRequest::findOrFail($repair_request_id);
    
    // ดึงตัวอักษรแรกจาก DeviceType และสร้าง Tag Number
    $deviceType = strtoupper(substr($repairRequest->device->DeviceType, 0, 1)); // รับเฉพาะตัวแรก
    $generatedTagNumber = $this->generateTagNumber($deviceType);
    
    // ดึง EquipmentNumber จาก repair_request
    $equipmentNumber = $repairRequest->EquipmentNumber;
    
    // ดึงข้อมูลอุปกรณ์จาก register_device โดยใช้ EquipmentNumber
    $deviceInfo = RegisterDevice::where('EquipmentNumber', $equipmentNumber)->first();

    // กำหนดค่า $buildings
    $buildings = Building::all(); // หรือจากข้อมูลที่คุณต้องการ
   
    // ส่งข้อมูลไปยัง view
    return view('repair_requests.register_tag', compact('repairRequest', 'deviceInfo', 'generatedTagNumber', 'buildings'));
}


    
public function storeTag(Request $request, $repair_request_id)
{
    try {
        // เพิ่มการตรวจสอบค่าจากฟอร์ม
        $validatedData = $request->validate([
            'TagNumber' => 'required|string|max:30',
            'brand' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'other_features' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        // ดึง EquipmentNumber จาก repair_request
        $repairRequest = RepairRequest::findOrFail($repair_request_id);
        $equipmentNumber = $repairRequest->EquipmentNumber;

        // บันทึกข้อมูลในตาราง register_tags
        $registerTag = RegisterTag::create([
            'repair_request_id' => $repair_request_id,
            'TagNumber' => $validatedData['TagNumber'],
            'EquipmentNumber' => $equipmentNumber,
            'brand' => $validatedData['brand'],
            'model' => $validatedData['model'],
            'other_features' => $validatedData['other_features'] ?? null,
            'location' => $validatedData['location'] ?? null,
        ]);

        // อัปเดต TagNumber ในตาราง repair_requests
        $repairRequest->update([
            'TagNumber' => $validatedData['TagNumber'],
        ]);

        // อัปเดต register_tag_id ในตาราง register_device
        $registerDevice = RegisterDevice::where('EquipmentNumber', $equipmentNumber)->first();
        if ($registerDevice) {
            $registerDevice->update([
                'register_tag_id' => $registerTag->id, // ใช้ id จาก register_tags ที่เพิ่งสร้าง
            ]);
        }

        // ทำการตอบกลับหรือรีไดเรคหลังจากบันทึกข้อมูล
        return redirect()->route('it')->with('success', 'ลงทะเบียนแท็กสำเร็จ');
    } catch (\Exception $e) {
        Log::error('Error storing tag: ' . $e->getMessage());
        return back()->withErrors(['error' => $e->getMessage()]);
    }
}


    public function registerTag(Request $request, $id)
    {
        $validatedData = $request->validate([
            'TagNumber' => 'required|string|max:30',
        ]);

        $repairRequest = RepairRequest::where('TicketID', $id)->firstOrFail();
        $repairRequest->update([
            'TagNumber' => $validatedData['TagNumber'],
        ]);

        return redirect()->route('it')->with('success', 'ลงทะเบียนแท็กสำเร็จ');
    }

        
    public function showStatusChangeForm($id)
    {
         $request = RepairRequest::where('TicketID', $id)->firstOrFail();
        $statuses = Status::all();
        
        return view('it.change_status', compact('request', 'statuses'));
    }
    
    
    // ฟังก์ชันสำหรับบันทึกการเปลี่ยนสถานะ
public function updateStatus(Request $request, $repairRequest) 
{
    $validatedData = $request->validate([
        'status_id' => 'required|exists:statuses,StatusID',
        'note' => 'nullable|string|max:255',
        'start' => 'required|date', // Validate start time for schedule
        'color' => 'nullable|string|max:7', // Optional color input
    ]);

    // ดึงข้อมูลคำร้องซ่อมจาก TicketID
    $repairRequest = RepairRequest::where('TicketID', $repairRequest)->firstOrFail();
    $oldStatusId = $repairRequest->StatusID;

    // ตรวจสอบว่าเปลี่ยนสถานะทีละขั้นเท่านั้น
    if (($oldStatusId == 0 && $validatedData['status_id'] != 2) || 
        ($oldStatusId == 2 && $validatedData['status_id'] != 3)) {
        return redirect()->back()->with('error', 'คุณต้องเปลี่ยนสถานะทีละขั้นเท่านั้น');
    }

    // อัปเดตสถานะ
    $repairRequest->update(['StatusID' => $validatedData['status_id']]);

    // บันทึกโน้ตใหม่โดยตรงในตาราง repair_requests
    if (!empty($validatedData['note'])) {
        $repairRequest->update(['note' => $validatedData['note']]); 
    }

    // หากสถานะเปลี่ยนเป็น 2, สร้างกิจกรรมใหม่ใน Schedule
    if ($validatedData['status_id'] == 2) {
        Schedule::create([
            'user_id' => Auth::id(),
            'title' => "" . $repairRequest->TicketNumber,
            'start' => Carbon::parse($validatedData['start'])->setTimezone('Asia/Bangkok'),
            'end' => Carbon::parse($validatedData['start'])->setTimezone('Asia/Bangkok'), // Set end to same as start
            'color' => $validatedData['color'],
            'TicketID' => $repairRequest->TicketID,
        ]);
    }

    // แจ้งเตือนผู้ใช้และผู้ดูแล
    $newStatus = Status::where('StatusID', $validatedData['status_id'])->first();
    $newStatusName = $newStatus ? $newStatus->Statusname : 'ไม่ทราบสถานะ';

    $message = "สถานะของคำร้องซ่อม TicketID: " . $repairRequest->TicketNumber . " ได้เปลี่ยนเป็น " . $newStatusName;

    // ส่งการแจ้งเตือนให้ผู้ใช้ที่สร้างคำร้อง
    $user = User::find($repairRequest->user_id);
    if ($user && $user->line_token) {
        $this->sendLineNotify($message, $user->line_token);
    }

    // แจ้งเตือนผู้ดูแลระบบ
    $admins = User::where('role', 0)->get();
    foreach ($admins as $admin) {
        if ($admin->line_token) {
            $this->sendLineNotify("ผู้ดูแลระบบ: " . $message, $admin->line_token);
        }
    }

    return redirect()->route('it')->with('success', 'อัปเดตสถานะเรียบร้อยแล้ว');
}




    public function filterByStatus(Request $request)
{
    $status = $request->get('status');

    $query = RepairRequest::query();

    if ($status) {
        $query->whereHas('status', function ($q) use ($status) {
            $q->where('Statusname', $status);
        });
    }

    $requests = $query->get();

    // ส่งข้อมูลตารางกลับมา
    return view('repair_requests.partials._table', compact('requests'));
}
    // In RepairRequestController.php

public function showNoteForm($id)
{
   $request = RepairRequest::where('TicketID', $id)->firstOrFail();
    $assignment = Assignment::where('TicketID', $id)->first();
     return view('repair_requests.note', compact('request', 'assignment'));
}
public function showNote($id)
{
    $request = RepairRequest::where('TicketID', $id)->firstOrFail();
     $assignment = Assignment::where('TicketID', $id)->first();
    return view('repair_requests.note', compact('request', 'assignment'));
}

public function storeNote(Request $request, $id)
{
    // ตรวจสอบว่าผู้ใช้ที่ล็อกอินอยู่เป็น IT หรือไม่
    if (Auth::user()->role != 1) {
        return redirect()->back()->with('error', 'You do not have permission to add notes.');
    }

    // อนุญาตให้หมายเหตุเป็นค่าว่าง
    $validatedData = $request->validate([
        'note' => 'nullable|string|max:255',  // ใช้ nullable เพื่อให้ฟิลด์หมายเหตุเป็นค่าว่างได้
    ]);

    $request = RepairRequest::where('TicketID', $id)->firstOrFail();
    $repairRequest->update([
        'note' => $validatedData['note'] ?? null,  // อัปเดตเป็น null ถ้าฟิลด์ว่าง
    ]);

    return redirect()->route('it') // เปลี่ยนเส้นทางให้เหมาะสม
                     ->with('success', 'Note added successfully.');
}

    // ฟังก์ชันสำหรับแสดงฟอร์มประเมิน
    public function showEvaluationForm($id)
    {
        $request = RepairRequest::where('TicketID', $id)->firstOrFail();
    
        // ตรวจสอบว่าสถานะเป็น "ดำเนินการแล้ว" หรือไม่
        if ($request->status->Statusname !== 'ดำเนินการแล้ว') {
            return redirect()->back()->with('error', 'สามารถประเมินได้ตอนแจ้งซ่อมเสร็จแล้วเท่านั้น.');
        }
    
        return view('repair_requests.evaluate', compact('request'));
    }
    

    // ฟังก์ชันสำหรับบันทึกการประเมิน
    public function storeEvaluation(Request $request, $id)
    {
        // Validate input
        $validatedData = $request->validate([
            'rating' => 'required|integer|min:1|max:3', // Rating 1-3
        ]);
    
        // Find the repair request
       $repairRequest = RepairRequest::where('TicketID', $id)->firstOrFail();
    
        // Check if the status is "ดำเนินการแล้ว"
        if ($repairRequest->status->Statusname !== 'ดำเนินการแล้ว') {
            return redirect()->back()->with('error', 'สามารถประเมินได้ตอนแจ้งซ่อมเสร็จแล้วเท่านั้น.');
        }
    
        // Check if there's already an evaluation
        if ($repairRequest->evaluation()->exists()) {
            return redirect()->back()->with('error', 'ไม่สามารถทำการประเมินซ้ำได้.');
        }
    
        // Save the evaluation
        Evaluation::create([
            'repair_request_id' => $repairRequest->TicketID,
            'user_id' => Auth::id(),
            'rating' => $validatedData['rating'],
        ]);
    
        // Update the is_evaluated flag
        $repairRequest->is_evaluated = true;
        $repairRequest->save();
    
        // Debugging: Log the state after saving
       
    
        return redirect()->route('repair_request.index')->with('success', 'ประเมินผลเรียบร้อยแล้ว');
    }
    
    public function viewEvaluations()
{
    $user = Auth::user();

    // Check if the user is an Admin
    if ($user->role != 0) {
        return redirect()->route('dashboard')->with('error', 'Only admins can view evaluations.');
    }

    // Get all evaluations along with related repair requests
    $evaluations = Evaluation::with('repairRequest', 'user')->get();

    return view('repair_requests.evaluations', compact('evaluations'));
}



}