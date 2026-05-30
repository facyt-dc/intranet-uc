import React from 'react';
import { Link, Head, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Button from "@mui/material/Button";
import EditIcon from "@mui/icons-material/Edit";
import DeleteIcon from "@mui/icons-material/Delete";
import { useTranslation } from "react-i18next";


export default function Index({ auth, statuses }) {
    const { t } = useTranslation(["translation"]);
    const { delete: destroy } = useForm();

    const handleDelete = (id) => {
        if (confirm("¿Estás seguro que deseas eliminar esta ubicación?")) {
            destroy(route("admin.item-status.destroy", id));
        }
    };

    return (
        <AdminLayout auth={auth}>
            <Head title="Estados de Ítems" />
            <div className="p-6">
                <h1 className="text-2xl font-bold mb-6">Estados de Ítems</h1>

                <div className="flex justify-between items-center mb-6">
                    <Link
                        href={route('admin.item-status.create')}
                        className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    >
                        Crear Estado
                    </Link>
                </div>

                {statuses.data.length === 0 ? (
                    <p className="mt-4 text-gray-500">No hay estados para mostrar.</p>
                ) : (
                    <table className="w-full border border-gray-300 table-auto">
                        <thead className="bg-gray-100">
                            <tr>
                                <th className="border px-4 py-2 text-left">ID</th>
                                <th className="border px-4 py-2 text-left">Nombre</th>
                                <th className="border px-4 py-2 text-left">Descripción</th>
                                <th className="border px-4 py-2 text-center">Operativo</th>
                                <th className="border px-4 py-2 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {statuses.data.map((status) => (
                                <tr key={status.id} className="border-t hover:bg-gray-50">
                                    <td className="border px-4 py-2">{status.id}</td>
                                    <td className="border px-4 py-2">{status.name}</td>
                                    <td className="border px-4 py-2">{status.description}</td>
                                    <td className="border px-4 py-2 text-center">
                                        {status.is_operational ? 'Sí' : 'No'}
                                    </td>
                                    <td className="p-2 border text-center space-x-2">
                                        <Link href={route('admin.item-status.edit', status.id)}>
                                            <Button
                                                variant="contained"
                                                size="small"
                                                startIcon={<EditIcon />}
                                            >
                                                {t("Edit")}
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="outlined"
                                            color="error"
                                            size="small"
                                            startIcon={<DeleteIcon />}
                                            onClick={() => handleDelete(status.id)}
                                        >
                                            {t("Delete")}
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}


                <div className="mt-4 flex justify-between items-center">
                    <div className="text-sm text-gray-500">
                        Mostrando {statuses.from} a {statuses.to} de {statuses.total} resultados
                    </div>
                    <div className="flex space-x-2">
                        {statuses.links.map((link, index) => (
                            <Link
                                key={index}
                                href={link.url}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                                className={`px-4 py-2 border rounded ${!link.url ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : ''
                                    } ${link.active ? 'bg-blue-600 text-white' : 'bg-white'}`}
                            />
                        ))}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}