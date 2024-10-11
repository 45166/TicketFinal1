<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RegisterDevice;
use App\Models\Device; // Import the Device model

class RegisterDeviceController extends Controller
{
public function index()
{
    // ดึงข้อมูลอุปกรณ์ทั้งหมด
    $devices = RegisterDevice::all();
 $devices = RegisterDevice::with('tags')->get(); 
    // ส่งตัวแปรไปยัง view
    return view('register_device.index', compact('devices'));
}
    public function create()
    {
        // Fetch all devices from the 'devices' table
        $devices = Device::all();

        // Pass the devices to the view
        return view('register_device.create', compact('devices'));
    }

    public function store(Request $request)
    {
        // Validate ข้อมูลที่กรอก
        $validated = $request->validate([
            'EquipmentNumber' => 'required|string|max:255|unique:register_device',
            'Brand' => 'required|string|max:255',
            'Model' => 'required|string|max:255',
            'DeviceID' => 'required|exists:devices,DeviceID', // Validation for DeviceID
            'OtherFeatures' => 'nullable|string|max:255',
            'location' => 'required|string|max:255',
        ]);

        // บันทึกข้อมูลลงฐานข้อมูล
        RegisterDevice::create($validated);

        return redirect()->route('register_device.create')->with('success', 'Device registered successfully.');
    }

   public function edit($equipmentNumber)
{
    $device = RegisterDevice::where('EquipmentNumber', $equipmentNumber)->firstOrFail();
    $devices = Device::all();

    return view('register_device.edit', compact('device', 'devices'));
}

public function update(Request $request, $EquipmentNumber)
{
    $validated = $request->validate([
        'Brand' => 'required|string|max:50',
        'Model' => 'required|string|max:50',
        'OtherFeatures' => 'nullable|string|max:255',
        'location' => 'required|string|max:255',
    ]);

    // ค้นหาข้อมูลอุปกรณ์ตาม EquipmentNumber
    $device = RegisterDevice::where('EquipmentNumber', $EquipmentNumber)->firstOrFail();

    // อัปเดตข้อมูลอุปกรณ์
    $device->update($validated);

    return redirect()->route('register_device.index')->with('success', 'Device updated successfully.');
}

    public function destroy($id)
    {
        $device = RegisterDevice::findOrFail($id);
        $device->delete();

        return redirect()->route('register_device.index')->with('success', 'Device deleted successfully!');
    }
}
