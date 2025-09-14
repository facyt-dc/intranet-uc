import React, { useState } from 'react';
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, useForm } from '@inertiajs/react';
import { format, parseISO } from 'date-fns';
import { useTranslation, Trans } from 'react-i18next';

// Componente para una Pestaña de Navegación
const TabButton = ({ isActive, onClick, children }) => (
    <button
        onClick={onClick}
        className={`px-4 py-2 text-sm font-medium border-b-2 ${
            isActive
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
        }`}
    >
        {children}
    </button>
);

// Componente para un campo del formulario (vista/edición)
const FormField = ({ label, children, isEditing, viewValue }) => (
    <div>
        <label className="block text-sm font-medium text-gray-700">{label}</label>
        {isEditing ? (
            <div className="mt-1">{children}</div>
        ) : (
            <p className="mt-1 text-base text-gray-900 h-10 flex items-center">{viewValue || <span className="text-gray-400">
                    <Trans i18nKey="NotDefined">
                        Not defined
                    </Trans>
                </span>}
            </p>
        )}
    </div>
);

const formatDateForInput = (dateString) => {
    if (!dateString) {
        return '';
    }
    try {
        // parseISO convierte el string a un objeto Date de JavaScript
        // format le da el formato que el input necesita
        return format(parseISO(dateString), 'yyyy-MM-dd');
    } catch (error) {
        // Si hay un error, devuelve el string original para no romper el formulario
        return dateString;
    }
};


export default function Form({ auth, equipment, categories, isEditingDefault = false }) {
    const isCreateMode = !equipment;
    const [isEditing, setIsEditing] = useState(isCreateMode || isEditingDefault);
    const [activeTab, setActiveTab] = useState('general');
    const { t } = useTranslation(); 
    const { data, setData, post, put, processing, errors, reset } = useForm({
        name: equipment?.name || '',
        brand: equipment?.brand || '',
        model: equipment?.model || '',
        serial_number: equipment?.serial_number || '',
        description: equipment?.description || '',
        equipment_category_id: equipment?.equipment_category_id || '',
        last_maintained_at: formatDateForInput(equipment?.last_maintained_at),
        next_maintenance_at: formatDateForInput(equipment?.next_maintenance_at),
        maintenance_frequency: equipment?.maintenance_frequency || '',
        maintenance_interval: equipment?.maintenance_interval || '',
        last_failure_at: formatDateForInput(equipment?.last_failure_at),
        mtbf: equipment?.mtbf || '',
        mttr: equipment?.mttr || '',
    });

    const intervalMap = {
        days: t('Days'),
        months: t('Months'),
        years: t('Years'),
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (isCreateMode) {
            post(route('mantenimiento.equipment.store'));
        } else {
            put(route('mantenimiento.equipment.update', equipment.id), {
                onSuccess: () => setIsEditing(false), // Salir del modo edición al guardar
            });
        }
    };

    const pageTitle = isCreateMode ? t('Create Equipment') : equipment.name;

    return (
        <AdminLayout
            auth={auth}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">{pageTitle}</h2>
                </div>
            }
        >
            <Head title={pageTitle} />

            <div className="py-12">
                <div className="flex items-center gap-4 max-w-7xl mx-auto sm:px-6 lg:px-8 mb-5">
                    <Link href={route('mantenimiento.equipment.index')} className="text-sm font-semibold">&larr; {t('Back to List')}</Link>
                    {!isCreateMode && (
                        isEditing ? (
                            <button onClick={() => setIsEditing(false)} className="text-sm text-gray-600">{t('Cancel')}</button>
                        ) : (
                            <button onClick={() => setIsEditing(true)} className="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-500">{t('Edit')}</button>
                        )
                    )}
                    {isEditing && (
                        <button onClick={handleSubmit} disabled={processing} className="px-4 py-2 bg-gray-800 text-white rounded-md text-sm font-semibold hover:bg-gray-700 disabled:bg-gray-400">
                            {processing ? t('Saving...') : t('Save')}
                        </button>
                    )}
                </div>
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200">
                            <nav className="-mb-px flex space-x-4 px-6" aria-label="Tabs">
                                <TabButton isActive={activeTab === 'general'} onClick={() => setActiveTab('general')}>{t('General Information')}</TabButton>
                                <TabButton isActive={activeTab === 'maintenance'} onClick={() => setActiveTab('maintenance')}>{t('Maintenance')}</TabButton>
                                <TabButton isActive={activeTab === 'tracking'} onClick={() => setActiveTab('tracking')}>{t('Tracking & Failures')}</TabButton>
                                {!isCreateMode && (
                                    <TabButton isActive={activeTab === 'history'} onClick={() => setActiveTab('history')}>{t('Maintenance History')}</TabButton>
                                )}
                            </nav>
                        </div>
                        <form onSubmit={handleSubmit} className="p-6">
                            {/* Pestaña General */}
                            <div className={`${activeTab !== 'general' && 'hidden'} grid grid-cols-1 md:grid-cols-2 gap-6`}>
                                <FormField label={t('Equipment Name')} isEditing={isEditing} viewValue={data.name}>
                                    <input type="text" value={data.name} onChange={e => setData('name', e.target.value)} className="form-input w-full" />
                                </FormField>
                                <FormField label={t('Category')} isEditing={isEditing} viewValue={equipment?.category?.name}>
                                    <select value={data.equipment_category_id} onChange={e => setData('equipment_category_id', e.target.value)} className="form-select w-full">
                                        <option value="">{t('No category')}</option>
                                        {categories.map(cat => <option key={cat.id} value={cat.id}>{cat.name}</option>)}
                                    </select>
                                </FormField>
                                <FormField label={t('Brand')} isEditing={isEditing} viewValue={data.brand}><input type="text" value={data.brand} onChange={e => setData('brand', e.target.value)} className="form-input w-full" /></FormField>
                                <FormField label={t('Model')} isEditing={isEditing} viewValue={data.model}><input type="text" value={data.model} onChange={e => setData('model', e.target.value)} className="form-input w-full" /></FormField>
                                <FormField label={t('Serial Number')} isEditing={isEditing} viewValue={data.serial_number}><input type="text" value={data.serial_number} onChange={e => setData('serial_number', e.target.value)} className="form-input w-full" /></FormField>
                                <div className="md:col-span-2">
                                    <FormField label={t('Description')} isEditing={isEditing} viewValue={data.description}><textarea rows="4" value={data.description} onChange={e => setData('description', e.target.value)} className="form-textarea w-full"></textarea></FormField>
                                </div>
                            </div>

                            {/* Pestaña Mantenimiento */}
                            <div className={`${activeTab !== 'maintenance' && 'hidden'} grid grid-cols-1 md:grid-cols-2 gap-6`}>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">{t('Last Maintenance')}</label>
                                    <p className="mt-1 text-base text-gray-900 h-10 flex items-center">{equipment?.last_maintained_at ? format(parseISO(equipment.last_maintained_at), 'dd/MM/yyyy') : <span className="text-gray-400">{t('Not defined')}</span>}</p>
                                </div>
                                <FormField label={t('Next Maintenance')} isEditing={isEditing} viewValue={equipment?.next_maintenance_at ? format(parseISO(equipment.next_maintenance_at), 'dd/MM/yyyy') : undefined}>
                                    <input type="date" value={data.next_maintenance_at} onChange={e => setData('next_maintenance_at', e.target.value)} className="form-input w-full" />
                                </FormField>
                                <div className="md:col-span-2 grid grid-cols-2 gap-4 border-t pt-4">
                                    <FormField label={t('Maintenance every')} isEditing={isEditing} viewValue={data.maintenance_frequency}>
                                        <input type="number" value={data.maintenance_frequency} onChange={e => setData('maintenance_frequency', e.target.value)} placeholder={t('e.g., 30')} className="form-input w-full"/>
                                    </FormField>
                                    <FormField label={t('Interval')} isEditing={isEditing} viewValue={intervalMap[data.maintenance_interval] || data.maintenance_interval}>
                                        <select value={data.maintenance_interval} onChange={e => setData('maintenance_interval', e.target.value)} className="form-select w-full">
                                            <option value="">{t('Select...')}</option>
                                            <option value="days">{t('Days')}</option>
                                            <option value="months">{t('Months')}</option>
                                            <option value="years">{t('Years')}</option>
                                        </select>
                                    </FormField>
                                </div>
                            </div>

                            {/* Pestaña Seguimiento */}
                            <div className={`${activeTab !== 'tracking' && 'hidden'} grid grid-cols-1 md:grid-cols-2 gap-6`}>
                                <FormField label={t('Last Recorded Failure')} isEditing={isEditing} viewValue={data.last_failure_at}><input type="date" value={data.last_failure_at} onChange={e => setData('last_failure_at', e.target.value)} className="form-input w-full" /></FormField>
                                <FormField label={t('Mean Time Between Failures (MTBF in days)')} isEditing={isEditing} viewValue={data.mtbf}><input type="number" value={data.mtbf} onChange={e => setData('mtbf', e.target.value)} className="form-input w-full" /></FormField>
                                <FormField label={t('Mean Time To Repair (MTTR in hours)')} isEditing={isEditing} viewValue={data.mttr}><input type="number" value={data.mttr} onChange={e => setData('mttr', e.target.value)} className="form-input w-full" /></FormField>
                            </div>

                            {/* Pestaña Historial */}
                            <div className={`${activeTab !== 'history' && 'hidden'} space-y-4`}>
                                {equipment?.maintenance_requests && equipment.maintenance_requests.length > 0 ? (
                                    equipment.maintenance_requests.map(request => (
                                        <div key={request.id} className="p-4 border rounded-md flex justify-between items-start gap-4">
                                            <div className="flex-grow">
                                                <p className="font-bold text-gray-800">{request.title}</p>
                                                <p className="text-sm text-gray-600 mt-1">{request.description.substring(0, 150)}...</p>
                                                <div className="mt-2 flex items-center gap-4 text-xs text-gray-500">
                                                    <span>{t('Reported by:')} <strong>{request.user?.name || 'N/A'}</strong></span>
                                                    <span>{t('Status:')} <strong>{request.stage?.name || 'N/A'}</strong></span>
                                                    <span>{t('Date:')} <strong>{new Date(request.created_at).toLocaleDateString()}</strong></span>
                                                </div>
                                            </div>
                                            <Link href={route('mantenimiento.show', request.id)} className="px-3 py-1 bg-blue-500 text-white rounded-md text-sm whitespace-nowrap hover:bg-blue-600 transition">{t('View Details')}</Link>
                                        </div>
                                    ))
                                ) : (
                                    <p className="text-gray-500">{t('No maintenance history for this equipment.')}</p>
                                )}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}