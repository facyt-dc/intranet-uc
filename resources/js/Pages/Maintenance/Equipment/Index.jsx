import React from 'react';
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, equipments }) {

    const handleRowClick = (equipmentId) => {
        router.visit(route('mantenimiento.equipment.show', equipmentId));
    };

    return (
        <AdminLayout
            auth={auth}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">Equipos de Mantenimiento</h2>
                    
                </div>
            }
        >
            <Head title="Equipos" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-5">
                    <Link href={route('mantenimiento.equipment.create')} className="px-4 py-2 bg-gray-800 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-700">
                        Crear Equipo
                    </Link>
                </div>
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marca / Modelo</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Próx. Mantenimiento</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {equipments.map((item) => (
                                    <tr key={item.id} onClick={() => handleRowClick(item.id)} className="cursor-pointer hover:bg-gray-50 transition-colors duration-150">
                                        <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{item.name}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-gray-600">{item.brand || 'N/A'} / {item.model || 'N/A'}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-gray-600">{item.category}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-gray-600">{item.next_maintenance_at}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            {/* El botón de editar ahora lleva a la misma vista, pero activando el modo edición */}
                                            <Link href={route('mantenimiento.equipment.edit', item.id)} className="text-indigo-600 hover:text-indigo-900" onClick={(e) => e.stopPropagation()}>
                                                Editar
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                                {equipments.length === 0 && (
                                    <tr>
                                        <td colSpan="5" className="px-6 py-4 text-center text-gray-500">No se encontraron equipos.</td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}