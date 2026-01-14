<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItemController extends Controller {

    public function index(Request $request) {
        $items = Item::all();
        return Inertia::render('Items/Index', [
            'items' => $items,
        ]);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required|string|unique:items,name',
            'unit' => 'required|string',
            'quantity' => 'required|numeric|min:0',
        ]);

        $item = Item::create($data);

        InventoryTransaction::create([
            'item_id' => $item->id,
            'type' => 'addition',
            'quantity' => $data['quantity'],
        ]);

        return redirect()->back()->with('success', 'Item added successfully');
    }

    public function updateQuantity(Request $request, Item $item) {
        $data = $request->validate([
            'type' => 'required|in:addition,deduction',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        if ($data['type'] === 'deduction' && $data['quantity'] > $item->quantity) {
            return redirect()->back()->withErrors(['quantity' => 'Cannot deduct more than available']);
        }

        $item->quantity = $data['type'] === 'addition' 
            ? $item->quantity + $data['quantity']
            : $item->quantity - $data['quantity'];

        $item->save();

        InventoryTransaction::create([
            'item_id' => $item->id,
            'type' => $data['type'],
            'quantity' => $data['quantity'],
        ]);

        return redirect()->back()->with('success', 'Inventory updated successfully');
    }

    public function history(Item $item) {
        $transactions = $item->transactions()->orderBy('created_at', 'desc')->get();
        return Inertia::render('Items/History', [
            'item' => $item,
            'transactions' => $transactions,
        ]);
    }

    public function addMultiple(Request $request)
    {
        $items = $request->input('items', []);
        $inserted = [];

        foreach ($items as $item) {
            if (
                !empty($item['name']) &&
                !empty($item['unit']) &&
                isset($item['quantity']) &&
                $item['quantity'] > 0
            ) {
                // Prevent duplicate names (case-insensitive)
                $exists = \App\Models\Item::whereRaw('LOWER(name) = ?', [strtolower($item['name'])])->exists();
                if (!$exists) {
                    $inserted[] = [
                        'name' => $item['name'],
                        'unit' => $item['unit'],
                        'quantity' => $item['quantity'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (!empty($inserted)) {
            \App\Models\Item::insert($inserted);
        }

        return redirect()->back()->with('success', 'Items added successfully!');
    }
}
