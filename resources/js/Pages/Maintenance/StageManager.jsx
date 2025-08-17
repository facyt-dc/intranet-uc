import React, { useState } from 'react';
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, useForm, router } from '@inertiajs/react';

// Componente para una sola fila de la lista de etapas
const StageRow = ({ stage }) => {
    const [isEditing, setIsEditing] = useState(false);
    const { data, setData, put, processing, errors, reset } = useForm({
        name: stage.name,
        sequence: stage.sequence,
        is_final_stage: stage.is_final_stage || false,
    });

    const handleUpdate = (e) => {
        e.preventDefault();
        put(route('mantenimiento.stages.update', stage.id), {
            onSuccess: () => setIsEditing(false),
            preserveScroll: true,
        });
    };

    const handleDelete = () => {
        if (confirm(`¿Estás seguro de que quieres eliminar la etapa "${stage.name}"? Esta acción no se puede deshacer.`)) {
            router.delete(route('api.maintenance-stages.destroy', stage.id));
        }
    };

    return (
        <div className="flex items-center justify-between p-4 border-b">
            {isEditing ? (
                <form onSubmit={handleUpdate} className="flex-grow flex items-center gap-4">
                    <input type="text" value={data.name} onChange={e => setData('name', e.target.value)} className="form-input rounded-md shadow-sm flex-grow" />
                    <input type="number" value={data.sequence} onChange={e => setData('sequence', e.target.value)} className="form-input rounded-md shadow-sm w-20" />
                    <div className="flex items-center">
                        <input
                            type="checkbox"
                            checked={data.is_final_stage}
                            onChange={e => setData('is_final_stage', e.target.checked)}
                            className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <label className="ml-2 block text-sm text-gray-900">Etapa Final</label>
                    </div>
                    <button type="submit" disabled={processing} className="px-3 py-1 bg-blue-500 text-white rounded-md text-sm">Guardar</button>
                    <button type="button" onClick={() => setIsEditing(false)} className="text-sm text-gray-600">Cancelar</button>
                </form>
            ) : (
                <>
                    <div className="flex-grow">
                        <span className="font-bold">{stage.name}</span>
                        <span className="text-sm text-gray-500 ml-4">(Secuencia: {stage.sequence})</span>
                        {stage.is_final_stage ? (<span className="ml-2 text-xs font-semibold text-white bg-green-500 px-2 py-1 rounded-full">Finalizado</span>) : (<span className="ml-2 text-xs font-semibold text-white bg-green-500 px-2 py-1 rounded-full">En curso</span>)}
                    </div>
                    <div className="flex items-center gap-4">
                        <button onClick={() => setIsEditing(true)} className="text-sm font-medium text-indigo-600 hover:text-indigo-900">Editar</button>
                        <button onClick={handleDelete} className="text-sm font-medium text-red-600 hover:text-red-900">Eliminar</button>
                    </div>
                </>
            )}
        </div>
    );
};

// Componente principal de la página
export default function StageManager({ auth, stages }) {
    const { data: newData, setData: setNewData, post, processing: newProcessing, errors: newErrors, reset: resetNew } = useForm({
        name: '',
        sequence: (stages.length > 0 ? Math.max(...stages.map(s => s.sequence)) + 1 : 1), // Secuencia sugerida
    });

    const handleCreate = (e) => {
        e.preventDefault();
        post(route('mantenimiento.stages.store'), {
            onSuccess: () => resetNew(),
        });
    };

    return (
        <AdminLayout
            auth={auth}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">Gestionar Etapas de Mantenimiento</h2>
                     <Link href={route('mantenimiento.index')} className="text-sm font-semibold">&larr; Volver al Tablero</Link>
                </div>
            }
        >
            <Head title="Gestionar Etapas" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    {/* Formulario para crear nuevas etapas */}
                    <div className="bg-white p-6 rounded-lg shadow">
                        <h3 className="text-lg font-bold mb-4">Crear Nueva Etapa</h3>
                        <form onSubmit={handleCreate} className="flex items-start gap-4">
                            <div className="flex-grow">
                                <label htmlFor="newName" className="sr-only">Nombre de la Etapa</label>
                                <input id="newName" type="text" placeholder="Nombre de la nueva etapa" value={newData.name} onChange={e => setNewData('name', e.target.value)} className="form-input w-full rounded-md shadow-sm" />
                                {newErrors.name && <p className="text-sm text-red-600 mt-1">{newErrors.name}</p>}
                            </div>
                            <div className="w-24">
                               <label htmlFor="newSequence" className="sr-only">Secuencia</label>
                               <input id="newSequence" type="number" placeholder="Seq." value={newData.sequence} onChange={e => setNewData('sequence', e.target.value)} className="form-input w-full rounded-md shadow-sm" />
                               {newErrors.sequence && <p className="text-sm text-red-600 mt-1">{newErrors.sequence}</p>}
                            </div>
                            <div className="flex items-center">
                                <input
                                    type="checkbox"
                                    checked={newData.is_final_stage}
                                    onChange={e => setData('is_final_stage', e.target.checked)}
                                    className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <label className="ml-2 block text-sm text-gray-900">Etapa Final</label>
                            </div>
                            <button type="submit" disabled={newProcessing} className="px-4 py-2 bg-gray-800 text-white rounded-md font-semibold text-sm hover:bg-gray-700 disabled:bg-gray-400">
                                {newProcessing ? 'Creando...' : 'Crear'}
                            </button>
                        </form>
                    </div>

                    {/* Lista de etapas existentes */}
                    <div className="bg-white rounded-lg shadow">
                        {stages.map(stage => (
                            <StageRow key={stage.id} stage={stage} />
                        ))}
                        {stages.length === 0 && <p className="p-4 text-gray-500">No hay etapas definidas.</p>}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}