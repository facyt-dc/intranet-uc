import React, { useState } from 'react';
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useTranslation, Trans } from 'react-i18next';

const ConfirmDeleteModal = ({ isOpen, onClose, onConfirm, itemName, processing, errorMessage }) => {
    const { t } = useTranslation();

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity flex items-center justify-center z-50">
            <div className="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                {errorMessage ? (
                    <>
                        <h3 className="text-lg font-bold text-red-700">{t('Error Deleting')}</h3>
                        <div className="mt-2">
                            <p className="text-sm text-gray-800">{errorMessage}</p>
                        </div>
                        <div className="mt-6 flex justify-end">
                            <button type="button" onClick={onClose} className="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700">
                                {t('Continue')}
                            </button>
                        </div>
                    </>
                ) : (
                    <>
                        <h3 className="text-lg font-bold text-gray-900">{t('Confirm Deletion')}</h3>
                        <div className="mt-2">
                            <p className="text-sm text-gray-600">
                                <Trans i18nKey="confirmDeleteStageText" values={{ itemName }}>
                                    Are you sure you want to delete the stage <strong>"{itemName}"</strong>?<br/>This action cannot be undone.
                                </Trans>
                            </p>
                        </div>
                        <div className="mt-6 flex justify-end gap-4">
                            <button type="button" onClick={onClose} className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                {t('Cancel')}
                            </button>
                            <button type="button" onClick={onConfirm} disabled={processing} className="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 disabled:bg-red-300">
                                {processing ? t('Deleting...') : t('Confirm Deletion')}
                            </button>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};

const StageRow = ({ stage }) => {
    const { t } = useTranslation();
    const [isEditing, setIsEditing] = useState(false);
    const [isDeleteModalOpen, setDeleteModalOpen] = useState(false);
    const [modalErrorMessage, setModalErrorMessage] = useState(null);

    const { data, setData, put, processing } = useForm({
        name: stage.name,
        sequence: stage.sequence,
        is_final_stage: stage.is_final_stage,
    });
    const { delete: destroy, processing: isDeleting } = useForm();

    const handleUpdate = (e) => {
        e.preventDefault();
        put(route('mantenimiento.stages.update', stage.id), {
            onSuccess: () => setIsEditing(false),
            preserveScroll: true,
        });
    };

    const handleDeleteConfirm = () => {
        destroy(route('mantenimiento.stages.destroy', stage.id), {
            onSuccess: () => {
                setDeleteModalOpen(false);
                setModalErrorMessage(null);
            },
            onError: (errors) => {
                setModalErrorMessage(errors.error || t('An unknown error occurred.'));
            },
            preserveScroll: true,
        });
    };

    const openDeleteModal = () => {
        setModalErrorMessage(null);
        setDeleteModalOpen(true);
    };

    const closeDeleteModal = () => {
        setDeleteModalOpen(false);
        setModalErrorMessage(null);
    };

    return (
        <div className="flex items-center justify-between p-4 border-b">
            {isEditing ? (
                <form onSubmit={handleUpdate} className="flex-grow flex items-center gap-4">
                    <input type="text" value={data.name} onChange={e => setData('name', e.target.value)} className="form-input rounded-md shadow-sm flex-grow" />
                    <input type="number" value={data.sequence} onChange={e => setData('sequence', e.target.value)} className="form-input rounded-md shadow-sm w-20" />
                    <div className="flex items-center">
                        <input id={`final-stage-${stage.id}`} type="checkbox" checked={data.is_final_stage} onChange={e => setData('is_final_stage', e.target.checked)} className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"/>
                        <label htmlFor={`final-stage-${stage.id}`} className="ml-2 block text-sm text-gray-900">{t('Final Stage')}</label>
                    </div>
                    <button type="submit" disabled={processing} className="px-3 py-1 bg-blue-500 text-white rounded-md text-sm">{t('Save')}</button>
                    <button type="button" onClick={() => setIsEditing(false)} className="text-sm text-gray-600">{t('Cancel')}</button>
                </form>
            ) : (
                <>
                    <div className="flex items-center flex-grow">
                        <span className="font-bold">{stage.name}</span>
                        <span className="text-sm text-gray-500 ml-4">({t('Sequence')}: {stage.sequence})</span>
                        {stage.is_final_stage ? (
                            <span className="ml-4 text-xs font-semibold text-white bg-green-500 px-2 py-1 rounded-full">{t('Completed')}</span>
                        ) : (
                            <span className="ml-4 text-xs font-semibold text-white bg-yellow-500 px-2 py-1 rounded-full">{t('In Progress')}</span>
                        )}
                    </div>
                    <div className="flex items-center gap-4">
                        <button onClick={() => setIsEditing(true)} className="text-sm font-medium text-indigo-600 hover:text-indigo-900">{t('Edit')}</button>
                        <button onClick={openDeleteModal} className="text-sm font-medium text-red-600 hover:text-red-900">{t('Delete')}</button>
                    </div>
                </>
            )}
            <ConfirmDeleteModal
                isOpen={isDeleteModalOpen}
                onClose={closeDeleteModal}
                onConfirm={handleDeleteConfirm}
                itemName={stage.name}
                processing={isDeleting}
                errorMessage={modalErrorMessage}
            />
        </div>
    );
};

export default function StageManager({ auth, stages }) {
    const { t } = useTranslation();
    const { data: newData, setData: setNewData, post, processing: newProcessing, errors: newErrors, reset: resetNew } = useForm({
        name: '',
        sequence: (stages.length > 0 ? Math.max(...stages.map(s => s.sequence)) + 1 : 1),
        is_final_stage: false,
    });

    const handleCreate = (e) => {
        e.preventDefault();
        post(route('mantenimiento.stages.store'), {
            onSuccess: () => resetNew('name', 'sequence', 'is_final_stage'),
        });
    };

    return (
        <AdminLayout
            auth={auth}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">{t('Manage Maintenance Stages')}</h2>
                    <Link href={route('mantenimiento.index')} className="text-sm font-semibold">&larr; {t('Back to Kanban')}</Link>
                </div>
            }
        >
            <Head title={t('Manage Stages')} />
            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="bg-white p-6 rounded-lg shadow">
                        <h3 className="text-lg font-bold mb-4">{t('Create New Stage')}</h3>
                        <form onSubmit={handleCreate} className="flex items-start gap-4">
                            <div className="flex-grow">
                                <label htmlFor="newName" className="sr-only">{t('Name')}</label>
                                <input id="newName" type="text" placeholder={t('New stage name')} value={newData.name} onChange={e => setNewData('name', e.target.value)} className="form-input w-full rounded-md shadow-sm" />
                                {newErrors.name && <p className="text-sm text-red-600 mt-1">{newErrors.name}</p>}
                            </div>
                            <div className="w-24">
                                <label htmlFor="newSequence" className="sr-only">{t('Sequence')}</label>
                                <input id="newSequence" type="number" placeholder={t('Seq.')} value={newData.sequence} onChange={e => setNewData('sequence', e.target.value)} className="form-input w-full rounded-md shadow-sm" />
                                {newErrors.sequence && <p className="text-sm text-red-600 mt-1">{newErrors.sequence}</p>}
                            </div>
                            <div className="flex items-center pt-2">
                                <input id="newIsFinal" type="checkbox" checked={newData.is_final_stage} onChange={e => setNewData('is_final_stage', e.target.checked)} className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"/>
                                <label htmlFor="newIsFinal" className="ml-2 block text-sm text-gray-900">{t('Final Stage')}</label>
                            </div>
                            <button type="submit" disabled={newProcessing} className="px-4 py-2 bg-gray-800 text-white rounded-md font-semibold text-sm hover:bg-gray-700 disabled:bg-gray-400">
                                {newProcessing ? t('Creating...') : t('Create')}
                            </button>
                        </form>
                    </div>
                    <div className="bg-white rounded-lg shadow">
                        {stages.map(stage => (
                            <StageRow key={stage.id} stage={stage} />
                        ))}
                        {stages.length === 0 && <p className="p-4 text-gray-500">{t('No stages defined.')}</p>}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
