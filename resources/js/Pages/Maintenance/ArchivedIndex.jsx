import React, { useState, useEffect } from 'react';
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, router } from '@inertiajs/react';
import AdvancedFilterMenu from "@/Components/AdvancedFilterMenu"; // Reutilizamos el menú!

export default function ArchivedIndex({ auth, requests, technicians, equipments, equipmentCategories, filters }) {
    
    const [filterValues, setFilterValues] = useState({
        search: filters.search || '',
        technician: filters.technician || '',
        equipment: filters.equipment || '',
        category: filters.category || '',
    });

    useEffect(() => {
        const handler = setTimeout(() => {
            // Apunta a la nueva ruta de archivados
            router.get(route('mantenimiento.archived.index'), filterValues, {
                preserveState: true,
                replace: true,
            });
        }, 300);
        return () => clearTimeout(handler);
    }, [filterValues]);

    const handleFilterChange = (name, value) => {
        setFilterValues(prev => ({ ...prev, [name]: value }));
    };
    
    const resetFilters = () => {
        setFilterValues({ search: '', technician: '', equipment: '', category: '' });
    };

    return (
        <AdminLayout
            auth={auth}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">Solicitudes Archivadas</h2>
                    <Link href={route('mantenimiento.index')} className="text-sm font-semibold">&larr; Volver al Tablero</Link>
                </div>
            }
        >
            <Head title="Solicitudes Archivadas" />
            
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-5">
                    
                    <div className="flex items-center gap-4">
                        <input type="text" value={filterValues.search} onChange={(e) => handleFilterChange('search', e.target.value)} placeholder="Buscar en archivados..." className="form-input rounded-md shadow-sm text-sm w-full md:w-80" />
                        <div className="ml-auto">
                            <AdvancedFilterMenu
                                filterValues={filterValues}
                                onFilterChange={handleFilterChange}
                                onResetFilters={resetFilters}
                                technicians={technicians}
                                equipments={equipments}
                                equipmentCategories={equipmentCategories}
                            />
                        </div>
                        
                    </div>
                </div>
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipo</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Etapa</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {requests.map((req) => (
                                    <tr key={req.id}>
                                        <td className="px-6 py-4 font-medium text-gray-900">{req.title}</td>
                                        <td className="px-6 py-4 text-gray-600">{req.equipment?.name || 'N/A'}</td>
                                        <td className="px-6 py-4 text-gray-600">{req.stage?.name || 'N/A'}</td>
                                        <td className="px-6 py-4 text-right text-sm font-medium">
                                            <Link 
                                                href={route('mantenimiento.show', { maintenanceRequest: req.id, from: 'archived' })} 
                                                className="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Ver Detalles
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                                {requests.length === 0 && (
                                    <tr><td colSpan="4" className="px-6 py-4 text-center text-gray-500">No se encontraron solicitudes archivadas.</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}