<?php

namespace App\Http\Controllers;

use App\Models\OperatorUser;
use App\Models\MasterZone;
use App\Models\ZoneLog;
use App\Models\ZonePengawas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Imports\OperatorUsersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use App\Services\Production\OperatorUserService;


class OperatorUserController extends Controller
{
    public function __construct(
        private readonly OperatorUserService $operatorUserService
    ) {}

    public function showQr()
    {
        $qrCodes = $this->operatorUserService->getUsersWithQrCodes();
        return view('barcode.qr_operator', compact('qrCodes'));
    }

    public function showIdCard()
    {
        $qrCodes = $this->operatorUserService->getUsersWithIdCardData();
        return view('barcode.id_card', compact('qrCodes'));
    }

    public function index()
    {
        $users = OperatorUser::all();
        return view('operator.index', compact('users'));
    }

    public function updateProfilePicture(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:operator_user,id',
            'profile_picture' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = OperatorUser::findOrFail($request->user_id);

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('profile_pictures', $filename, 'public');

            // Delete old profile picture if exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Update user's profile picture
            $user->update(['profile_picture' => $path]);
        }

        return back()->with('success', 'Profile picture updated successfully.');
    }


    public function uploadForm()
    {
        return view('uploadExcelOperator');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        Excel::import(new OperatorUsersImport, $request->file('file'));

        return redirect()->back()->with('success', 'Users imported successfully!');
    }

    public function editZone()
    {
        $zones = MasterZone::all();
        $zoneData = ZonePengawas::all();
        $adjusters = OperatorUser::where('position', 'Adjuster')->get();
    
        return view('zonepengawas', compact('zones', 'adjusters', 'zoneData'));
    }

    public function updateZone(Request $request)
    {
        $request->validate([
            'zone_id' => 'required|exists:master_zone,id',
            'pengawas' => 'required|exists:operator_user,name',
            'shift' => 'required|in:1,2,3',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

         // Check if the record exists for this zone and shift
            $zonePengawas = ZonePengawas::where('zone_id', $request->zone_id)
                ->where('shift', $request->shift)
                ->first();

            if ($zonePengawas) {
            // Update existing record
                $zonePengawas->update([
                'pengawas' => $request->pengawas,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                ]);
            } else {
                // Create new record
                ZonePengawas::create([
                'zone_id' => $request->zone_id,
                'pengawas' => $request->pengawas,
                'shift' => $request->shift,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                ]);
            }

            ZoneLog::create([
                'zone_id' => $request->zone_id,
                'pengawas' => $request->pengawas,
                'shift' => $request->shift,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);
    

        return redirect()->back()->with('success', 'Zone updated successfully.');
    }

    public function showAllOperator()
    {
        $operators = OperatorUser::whereNotBetween('id', [1, 10])->get();
        return view('showalloperator', compact('operators'));
    }

    public function createOperator()
    {
        $departments = ['390', '351'];
        $positions = ['Operator', 'Adjuster', 'Setup Mold'];

        return view('create-operator', compact('departments', 'positions'));
    }

    public function storeOperator(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'department' => 'required|in:390,351',
            'position' => 'required|in:Operator,Adjuster,Setup Mold',
        ]);

        $password = Str::random(10);

        OperatorUser::create([
            'name' => $request->name,
            'password' => $password,
            'department' => $request->department,
            'position' => $request->position,
        ]);

        return redirect()->route('show.all.operators')->with('success', 'Operator berhasil ditambahkan!');
    }
}
