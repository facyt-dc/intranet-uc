import React, { useState, useEffect, Fragment } from "react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, useForm, router } from '@inertiajs/react';
import { format, parseISO } from 'date-fns';
import { useTranslation, Trans } from 'react-i18next';


const ConfirmDeleteModal = ({ isOpen, onClose, onConfirm, itemName, processing }) => {
    const { t } = useTranslation();
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div className="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h3 className="text-lg font-bold text-gray-900">{t('Confirm Permanent Deletion')}</h3>
                <div className="mt-2">
                    <p className="text-sm text-gray-600">
                        <Trans i18nKey="confirmDeleteRequestText" values={{ itemName }}>
                            Are you sure you want to permanently delete the request <strong>"{itemName}"</strong>? All associated attachments will also be deleted. This action cannot be undone.
                        </Trans>
                    </p>
                </div>
                <div className="mt-6 flex justify-end gap-4">
                    <button type="button" onClick={onClose} className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">{t('Cancel')}</button>
                    <button type="button" onClick={onConfirm} disabled={processing} className="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 disabled:bg-red-300">
                        {processing ? t('Deleting...') : t('Confirm Deletion')}
                    </button>
                </div>
            </div>
        </div>
    );
};


const AttachmentList = ({ attachments }) => {
    const { t } = useTranslation();
    if (!attachments || attachments.length === 0) {
        return <p className="text-sm text-gray-500">{t('No attachments.')}</p>;
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
                            {t('Download')}
                        </a>
                    </div>
                </li>
            ))}
        </ul>
    );
};

// Componente principal del formulario
export default function RequestForm({ auth, maintenanceRequest, users, technician, stages, equipments, came_from  }) {
    // Determina si estamos en modo de edición (si existe maintenanceRequest) o de creación.
    const { t } = useTranslation();
    const isEditMode = !!maintenanceRequest;
    const backUrl = came_from === 'archived' 
        ? route('mantenimiento.archived.index') 
        : route('mantenimiento.index');
    
    const backLinkText = came_from === 'archived' ? t('← Back to Archived') : t('← Back to Kanban');
    const [isEditing, setIsEditing] = useState(!isEditMode);
    const { delete: destroy, processing: isDeleting } = useForm();
    const [isDeleteModalOpen, setDeleteModalOpen] = useState(false);

    const formatDateForInput = (dateString) => {
        if (!dateString) return '';
        try {
            // Formato requerido: YYYY-MM-DDTHH:mm
            return format(parseISO(dateString), "yyyy-MM-dd'T'HH:mm");
        } catch (e) {
            return '';
        }
    };
    const { data, setData, post, processing, errors, reset } = useForm({
        title: maintenanceRequest?.title || '',
        description: maintenanceRequest?.description || '',
        type: maintenanceRequest?.type || 'corrective',
        user_id: maintenanceRequest?.user_id || auth.user.id,
        technician_id: maintenanceRequest?.technician_id || '',
        stage_id: maintenanceRequest?.stage_id || (stages.length > 0 ? stages[0].id : ''),
        attachments: null,
        equipment_id: maintenanceRequest?.equipment_id || '',
        duration: maintenanceRequest?.duration || 0,
        completion_date: formatDateForInput(maintenanceRequest?.completion_date),
    });

    const handleDeleteConfirm = () => {
        // La redirección se maneja en el backend
        destroy(route('mantenimiento.destroy', maintenanceRequest.id), {
            onSuccess: () => setDeleteModalOpen(false),
        });
    };
    const maintenanceTypeMap = {
        corrective: t('Corrective'),
        preventive: t('Preventive'),
    };
    const currentStage = stages.find(stage => stage.id == data.stage_id);
    const isCurrentStageFinal = currentStage?.is_final_stage || false;
    
    useEffect(() => {
        const newStage = stages.find(stage => stage.id == data.stage_id);
        // Si se mueve a una etapa final Y la fecha de finalización está vacía
        if (newStage?.is_final_stage && !data.completion_date) {
            // Ponemos la fecha y hora actual en el formato correcto
            setData('completion_date', format(new Date(), "yyyy-MM-dd'T'HH:mm"));
        }
    }, [data.stage_id]);

    const handleSubmit = (e) => {
        e.preventDefault();
        if (isEditMode) {
            post(route('mantenimiento.update', maintenanceRequest.id), {
                _method: 'put',
                preserveScroll: true,
                onSuccess: () => setIsEditing(false),
            });
        } else {
            post(route('mantenimiento.store'));
        }
    };
    const handleToggleArchive = () => {
        router.post(route('mantenimiento.toggleArchive', maintenanceRequest.id), {}, {
            preserveScroll: true,
        });
    };
    // Títulos y cabeceras dinámicas
    const pageTitle = isEditMode ? `${t('Request:')} ${maintenanceRequest.title}` : t('New Request');
    const headerTitle = isEditMode ? t('Request Details') : t('Create New Request');

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
                                <Link href={backUrl} className="text-sm font-semibold text-gray-600 hover:underline">
                                    {t('Cancel')}
                                </Link>
                                <button onClick={handleSubmit} disabled={processing} className="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-500 disabled:bg-blue-300">
                                    {processing ? t('Saving...') : (isEditMode ? t('Save Changes') : t('Create Request'))}
                                </button>
                            </>
                        ) : (
                            // Botones en modo de solo lectura (solo para solicitudes existentes)
                            <>
                                <Link href={backUrl} className="text-sm font-semibold text-gray-600 hover:underline">{backLinkText}</Link>
                                <button onClick={() => setIsEditing(true)} className="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold hover:bg-green-500 transition">{t('Edit')}</button>
                                <button 
                                    onClick={handleToggleArchive}
                                    className={`px-4 py-2 rounded-md text-sm font-semibold text-white transition ${maintenanceRequest.is_archived ? 'bg-green-500 hover:bg-green-600' : 'bg-yellow-500 hover:bg-yellow-600'}`}
                                >
                                    {maintenanceRequest.is_archived ? t('Restore from Archive') : t('Archive Request')}
                                </button>
                                <button onClick={() => setDeleteModalOpen(true)} className="ml-auto px-4 py-2 bg-red-600 text-white rounded-md text-sm font-semibold hover:bg-red-700 transition">
                                    {t('Delete Permanently')}
                                </button>
                            </>
                        )}
                    </div>
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <form onSubmit={handleSubmit} className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200 space-y-6">

                            {/* Título */}
                            <div>
                                {isEditing ? (
                                    <><label htmlFor="title" className="block text-sm font-bold text-gray-700">{t('Title')}</label><input id="title" type="text" value={data.title} onChange={e => setData('title', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-2xl font-bold" required />{errors.title && <p className="text-sm text-red-600 mt-1">{errors.title}</p>}</>
                                ) : (
                                    <h2 className="text-3xl font-bold text-gray-900">{maintenanceRequest.title}</h2>
                                )}
                            </div>
                            <div>
                                <label className="block text-sm font-bold text-gray-700">{t('Affected Equipment')}</label>
                                {isEditing ? (
                                    <select id="equipment_id" value={data.equipment_id} onChange={e => setData('equipment_id', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                         <option value="" disabled>{t('Select an equipment')}</option>
                                        {equipments.map(eq => <option key={eq.id} value={eq.id}>{eq.name}</option>)}
                                    </select>
                                ) : (
                                    <p className="mt-1">{maintenanceRequest.equipment?.name || 'Ninguno'}</p>
                                )}
                            </div>
                            {/* Descripción */}
                            <div>
                                {isEditing ? (
                                    <><label htmlFor="description" className="block text-sm font-bold text-gray-700">{t('Full Description')}</label><textarea id="description" rows="5" value={data.description} onChange={e => setData('description', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required></textarea>{errors.description && <p className="text-sm text-red-600 mt-1">{errors.description}</p>}</>
                                ) : (
                                    <div><h3 className="font-semibold text-gray-700">{t('Full Description')}</h3><p className="mt-1 text-gray-800 whitespace-pre-wrap">{maintenanceRequest.description}</p></div>
                                )}
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {/* Tipo de Mantenimiento */}
                                <div>
                                    <label className="block text-sm font-bold text-gray-700">{t('Maintenance Type')}</label>
                                    {isEditing ? (
                                        <select id="type" value={data.type} onChange={e => setData('type', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            <option value="corrective">{t('Corrective')}</option>
                                            <option value="preventive">{t('Preventive')}</option>
                                        </select>
                                    ) : (
                                        <p className="mt-1 capitalize">{maintenanceTypeMap[maintenanceRequest.type] || maintenanceRequest.type}</p>
                                    )}
                                </div>

                                {/* Estado (Solo visible, no editable aquí) */}

                                <div>
                                    <label className="block text-sm font-bold text-gray-700">{t('Current Stage')}</label>
                                    {isEditing ? (
                                        <select id="stage_id" value={data.stage_id} onChange={e => setData('stage_id', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            {stages.map(stage => <option key={stage.id} value={stage.id}>{stage.name}</option>)}
                                        </select>
                                        ) : (
                                        <p className="mt-1">{maintenanceRequest.stage?.name || 'N/A'}</p>
                                    )}
                                    {errors.stage_id && <p className="text-sm text-red-600 mt-1">{errors.stage_id}</p>}
                                </div>
                                {isCurrentStageFinal && (
                                    <>
                                        {/* Campo de Fecha de Finalización */}
                                        <div>
                                            <label htmlFor="completion_date" className="block text-sm font-bold text-gray-700">{t('Completion Date')}</label>
                                            {isEditing ? (
                                                <>
                                                    <input
                                                        id="completion_date"
                                                        type="datetime-local"
                                                        value={data.completion_date}
                                                        onChange={e => setData('completion_date', e.target.value)}
                                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                                    />
                                                    {errors.completion_date && <p className="text-sm text-red-600 mt-1">{errors.completion_date}</p>}
                                                </>
                                            ) : (
                                                <p className="mt-1">{maintenanceRequest.completion_date ? format(parseISO(maintenanceRequest.completion_date), 'dd/MM/yyyy HH:mm') : 'No definida'}</p>
                                            )}
                                        </div>

                                        {/* Campo de Duración */}
                                        <div>
                                            <label htmlFor="duration" className="block text-sm font-bold text-gray-700">{t('Duration (hours)')}</label>
                                            {isEditing ? (
                                                <>
                                                    <input
                                                        id="duration"
                                                        type="number"
                                                        step="0.1"
                                                        value={data.duration}
                                                        onChange={e => setData('duration', e.target.value)}
                                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                                        placeholder="Ej: 2.5"
                                                    />
                                                    {errors.duration && <p className="text-sm text-red-600 mt-1">{errors.duration}</p>}
                                                </>
                                            ) : (
                                                <p className="mt-1">{maintenanceRequest.duration ? `${maintenanceRequest.duration} horas` : t('Not defined')}</p>
                                            )}
                                        </div>
                                    </>
                                )}
                                {/* Reportado Por */}
                                <div>
                                    <label className="block text-sm font-bold text-gray-700">{t('Reported by')}</label>
                                    {isEditing ? (
                                        <select id="user_id" value={data.user_id} onChange={e => setData('user_id', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            {users.map(user => <option key={user.id} value={user.id}>{user.name}</option>)}
                                        </select>
                                    ) : (
                                        <p className="mt-1">{maintenanceRequest.user?.name || t('Unassigned')}</p>
                                    )}
                                </div>

                                {/* Técnico Asignado */}
                                <div>
                                    <label className="block text-sm font-bold text-gray-700">{t('Assigned Technician')}</label>
                                    {isEditing ? (
                                        <select id="technician_id" value={data.technician_id} onChange={e => setData('technician_id', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            <option value="">{t('Attachments')}</option>
                                            {technician.map(tech => <option key={tech.id} value={tech.id}>{tech.name}</option>)}
                                        </select>
                                    ) : (
                                        <p className="mt-1">{maintenanceRequest.technician?.name || t('Unassigned')}</p>
                                    )}
                                </div>
                            </div>

                            {/* Archivos Adjuntos */}
                            <div className="border-t pt-4 mt-6">
                                <h3 className="font-semibold text-gray-700 mb-2">{t('Attachments')}</h3>
                                {isEditMode && <AttachmentList attachments={maintenanceRequest.attachments} />}
                                {isEditing && (
                                    <div className="mt-4">
                                        <label htmlFor="attachments" className="block text-sm font-bold text-gray-700">{t('Add new files')}</label>
                                        <input type="file" multiple onChange={e => setData('attachments', e.target.files)} className="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100"/>
                                        {errors.attachments && <p className="text-sm text-red-600 mt-1">{errors.attachments}</p>}
                                    </div>
                                )}
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            {isEditMode && (
                <ConfirmDeleteModal
                    isOpen={isDeleteModalOpen}
                    onClose={() => setDeleteModalOpen(false)}
                    onConfirm={handleDeleteConfirm}
                    itemName={maintenanceRequest.title}
                    processing={isDeleting}
                />
            )}
        </AdminLayout>
    );
}