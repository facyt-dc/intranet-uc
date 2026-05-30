import React from 'react';
import { Link, Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Button from '@mui/material/Button';
import VisibilityIcon from '@mui/icons-material/Visibility'; // Ícono para "Ver Detalles"

export default function InventoryMovementIndex({ auth, movements }) {

    // Función para formatear la fecha para que sea más legible
    const formatDate = (dateString) => {
        const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        return new Date(dateString).toLocaleDateString('es-VE', options);
    };

    return (
        <AdminLayout auth={auth}>
            <Head title="Historial de Movimientos" />
            <div>
                <div className="flex justify-between items-center mb-4">
                    <h1 className="text-2xl font-bold">Historial de Movimientos de Inventario</h1>
                </div>
                
                <div className="overflow-x-auto">
                    <table className="table-auto w-full border mt-4">
                        <thead className="bg-gray-100">
                            <tr>
                                <th className="p-2 border">ID</th>
                                <th className="p-2 border">Ítem</th>
                                <th className="p-2 border">Tipo de Movimiento</th>
                                <th className="p-2 border">Descripción</th>
                                <th className="p-2 border">Usuario Responsable</th>
                                <th className="p-2 border">Fecha</th>
                                <th className="p-2 border">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {movements.data.map((movement) => (
                                <tr key={movement.id} className="border-t hover:bg-gray-50">
                                    <td className="p-2 border">{movement.id}</td>
                                    <td className="p-2 border">{movement.item?.name || 'N/A'}</td>
                                    <td className="p-2 border">{movement.movement_type?.name || 'N/A'}</td>
                                    <td className="p-2 border">{movement.description}</td>
                                    <td className="p-2 border">{movement.user?.name || 'N/A'}</td>
                                    <td className="p-2 border">{formatDate(movement.movement_date)}</td>
                                    <td className="p-2 border text-center">
                                        {/* Enlace a la vista de detalles (show) */}
                                        <Link href={route('admin.item-inventory-movement.show', movement.id)}>
                                            <Button
                                                variant="outlined"
                                                size="small"
                                                startIcon={<VisibilityIcon />}
                                            >
                                                Ver Detalles
                                            </Button>
                                        </Link>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Componente de Paginación */}
                <div className="mt-4 flex justify-between items-center">
                    <div className="text-sm text-gray-500">
                        Mostrando {movements.from} a {movements.to} de {movements.total} resultados
                    </div>
                    <div className="flex space-x-2">
                        {movements.links.map((link, index) => (
                            <Link
                                key={index}
                                href={link.url}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                                className={`px-4 py-2 border rounded ${
                                    !link.url ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ''
                                } ${link.active ? 'bg-blue-600 text-white' : 'bg-white'}`}
                            />
                        ))}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}