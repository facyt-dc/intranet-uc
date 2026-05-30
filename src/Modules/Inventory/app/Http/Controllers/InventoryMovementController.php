<?php

namespace Modules\Inventory\Http\Controllers;

use Modules\Inventory\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class InventoryMovementController extends Controller
{
    /**
     * Muestra un listado paginado del historial de movimientos de inventario.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $movements = InventoryMovement::with(['item', 'user', 'movementType'])
                                      ->orderByDesc('movement_date')
                                      ->paginate(10); // Paginamos los resultados para mejor rendimiento.

        // Renderiza la vista de Inertia, pasando los movimientos paginados.
        return Inertia::render('inventory::InventoryMovement/Index', [
            'movements' => $movements,
        ]);
    }

    /**
     * Muestra los detalles de un movimiento de inventario específico.
     *
     * @param  \Modules\Inventory\Models\InventoryMovement  $inventory_movement
     * @return \Inertia\Response
     */
    public function show(InventoryMovement $inventory_movement)
    {
        // Carga las relaciones del movimiento específico que se está viendo.
        $inventory_movement->load(['item', 'user', 'movementType']);

        return Inertia::render('inventory::InventoryMovement/Show', [
            'movement' => $inventory_movement,
        ]);
    }
}
