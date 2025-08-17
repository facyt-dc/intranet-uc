import React, { useState } from 'react';
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, useForm, router } from '@inertiajs/react';

// Componente para listar los archivos adjuntos existentes
const AttachmentList = ({ attachments }) => {
    if (!attachments || attachments.length === 0) {
        return <p className="text-sm text-gray-500">No hay archivos adjuntos.</p>;
    }
    return (
        <ul className="border border-gray-200 rounded-md divide-y divide-gray-200">
            {attachments.map(file => (
                <li key={file.id} className="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                    <div className="w-0 flex-1 flex items-center">
                        <svg className="flex-shrink-0 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fillRule="evenodd" d="M8 4a3 3 0 00-3 3v4a3 3 0 003 3h4a3 3 0 003-3V7a3 3 0 00-3-3H8zm-1.5 4a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z" clipRule="evenodd" />
                        </svg>
                        <span className="ml-2 flex-1 w-0 truncate">{file.original_name}</span>
                    </div>
                    <div className="ml-4 flex-shrink-0">
                        <a href={`/storage/${file.path}`} target="_blank" rel="noopener noreferrer" className="font-medium text-indigo-600 hover:text-indigo-500">
                            Descargar
                        </a>
                    </div>
                </li>
            ))}
        </ul>
    );
};

// Componente principal del formulario
export default function RequestForm({ auth, maintenanceRequest, users, technician, stages, equipments  }) {
    // Determina si estamos en modo de edición (si existe maintenanceRequest) o de creación.
    const isEditMode = !!maintenanceRequest;

    // Para una nueva solicitud, el formulario está activo por defecto.
    // Para una existente, empezamos en modo de visualización.
    const [isEditing, setIsEditing] = useState(!isEditMode);

    const { data, setData, post, processing, errors, reset } = useForm({
        title: maintenanceRequest?.title || '',
        description: maintenanceRequest?.description || '',
        type: maintenanceRequest?.type || 'corrective', // Valor por defecto
        user_id: maintenanceRequest?.user_id || auth.user.id, // Por defecto el usuario actual
        technician_id: maintenanceRequest?.technician_id || '',
        // Asigna el primer estado como inicial si no hay uno definido
        stage_id: maintenanceRequest?.stage_id || (stages.length > 0 ? stages[0].id : ''),
        attachments: null,
        equipment_id: maintenanceRequest?.equipment_id || '',
    });

    // Maneja el envío del formulario
    const handleSubmit = (e) => {
        e.preventDefault();
        if (isEditMode) {
            // Para actualizar, usamos 'post' con '_method: put' para el soporte de archivos.
            post(route('mantenimiento.update', maintenanceRequest.id), {
                _method: 'put',
                preserveScroll: true,
                onSuccess: () => setIsEditing(false), // Al éxito, salimos del modo edición
            });
        } else {
            // Para crear, hacemos un 'post' normal a la ruta 'store'.
            post(route('mantenimiento.store'));
        }
    };

    // Títulos y cabeceras dinámicas
    const pageTitle = isEditMode ? `Solicitud: ${maintenanceRequest.title}` : 'Nueva Solicitud';
    const headerTitle = isEditMode ? 'Detalle de la Solicitud' : 'Crear Nueva Solicitud';

    return (
        <AdminLayout
            auth={auth}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">{headerTitle}</h2>
                    
                </div>
            }
        >
            <Head title={pageTitle} />
            <div className="py-12">
                <div className="flex items-center gap-4 max-w-7xl mx-auto sm:px-6 lg:px-8 mb-5">
                        {isEditing ? (
                            // Botones cuando el formulario está activo (creando o editando)
                            <>
                                <Link href={route('mantenimiento.index')} className="text-sm font-semibold text-gray-600 hover:underline">
                                    Cancelar
                                </Link>
                                <button onClick={handleSubmit} disabled={processing} className="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-500 disabled:bg-blue-300">
                                    {processing ? 'Guardando...' : (isEditMode ? 'Guardar Cambios' : 'Crear Solicitud')}
                                </button>
                            </>
                        ) : (
                            // Botones en modo de solo lectura (solo para solicitudes existentes)
                            <>
                                <Link href={route('mantenimiento.index')} className="text-sm font-semibold text-gray-600 hover:underline">&larr; Volver al Tablero</Link>
                                <button onClick={() => setIsEditing(true)} className="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold hover:bg-green-500 transition">Editar</button>
                            </>
                        )}
                    </div>
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <form onSubmit={handleSubmit} className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200 space-y-6">

                            {/* Título */}
                            <div>
                                {isEditing ? (
                                    <><label htmlFor="title" className="block text-sm font-bold text-gray-700">Título</label><input id="title" type="text" value={data.title} onChange={e => setData('title', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-2xl font-bold" required />{errors.title && <p className="text-sm text-red-600 mt-1">{errors.title}</p>}</>
                                ) : (
                                    <h2 className="text-3xl font-bold text-gray-900">{maintenanceRequest.title}</h2>
                                )}
                            </div>
                            <div>
                                <label className="block text-sm font-bold text-gray-700">Equipo Afectado</label>
                                {isEditing ? (
                                    <select id="equipment_id" value={data.equipment_id} onChange={e => setData('equipment_id', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                        {equipments.map(eq => <option key={eq.id} value={eq.id}>{eq.name}</option>)}
                                    </select>
                                ) : (
                                    <p className="mt-1">{maintenanceRequest.equipment?.name || 'Ninguno'}</p>
                                )}
                            </div>
                            {/* Descripción */}
                            <div>
                                {isEditing ? (
                                    <><label htmlFor="description" className="block text-sm font-bold text-gray-700">Descripción Completa</label><textarea id="description" rows="5" value={data.description} onChange={e => setData('description', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required></textarea>{errors.description && <p className="text-sm text-red-600 mt-1">{errors.description}</p>}</>
                                ) : (
                                    <div><h3 className="font-semibold text-gray-700">Descripción Completa:</h3><p className="mt-1 text-gray-800 whitespace-pre-wrap">{maintenanceRequest.description}</p></div>
                                )}
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {/* Tipo de Mantenimiento */}
                                <div>
                                    <label className="block text-sm font-bold text-gray-700">Tipo de Mantenimiento</label>
                                    {isEditing ? (
                                        <select id="type" value={data.type} onChange={e => setData('type', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            <option value="corrective">Correctivo</option>
                                            <option value="preventive">Preventivo</option>
                                        </select>
                                    ) : (
                                        <p className="mt-1 capitalize">{maintenanceRequest.type}</p>
                                    )}
                                </div>

                                {/* Estado (Solo visible, no editable aquí) */}

                                <div>
                                    <label className="block text-sm font-bold text-gray-700">Estado Actual</label>
                                    {isEditing ? (
                                        <select id="stage_id" value={data.stage_id} onChange={e => setData('stage_id', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            {stages.map(stage => <option key={stage.id} value={stage.id}>{stage.name}</option>)}
                                        </select>
                                        ) : (
                                        <p className="mt-1">{maintenanceRequest.stage?.name || 'N/A'}</p>
                                    )}
                                </div>

                                {/* Reportado Por */}
                                <div>
                                    <label className="block text-sm font-bold text-gray-700">Reportado por</label>
                                    {isEditing ? (
                                        <select id="user_id" value={data.user_id} onChange={e => setData('user_id', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            {users.map(user => <option key={user.id} value={user.id}>{user.name}</option>)}
                                        </select>
                                    ) : (
                                        <p className="mt-1">{maintenanceRequest.user?.name || 'N/A'}</p>
                                    )}
                                </div>

                                {/* Técnico Asignado */}
                                <div>
                                    <label className="block text-sm font-bold text-gray-700">Técnico Asignado</label>
                                    {isEditing ? (
                                        <select id="technician_id" value={data.technician_id} onChange={e => setData('technician_id', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            <option value="">Sin Asignar</option>
                                            {technician.map(tech => <option key={tech.id} value={tech.id}>{tech.name}</option>)}
                                        </select>
                                    ) : (
                                        <p className="mt-1">{maintenanceRequest.technician?.name || 'Sin Asignar'}</p>
                                    )}
                                </div>
                            </div>

                            {/* Archivos Adjuntos */}
                            <div className="border-t pt-4 mt-6">
                                <h3 className="font-semibold text-gray-700 mb-2">Archivos Adjuntos</h3>
                                {isEditMode && <AttachmentList attachments={maintenanceRequest.attachments} />}
                                {isEditing && (
                                    <div className="mt-4">
                                        <label htmlFor="attachments" className="block text-sm font-bold text-gray-700">Añadir nuevos archivos</label>
                                        <input type="file" multiple onChange={e => setData('attachments', e.target.files)} className="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100"/>
                                        {errors.attachments && <p className="text-sm text-red-600 mt-1">{errors.attachments}</p>}
                                    </div>
                                )}
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}