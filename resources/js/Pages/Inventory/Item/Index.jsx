import React from 'react';
import { Link, Head, useForm } from '@inertiajs/react'; // Importa Head
import AdminLayout from '@/Layouts/AdminLayout'; // Importa el Layout
import Button from "@mui/material/Button";
import EditIcon from "@mui/icons-material/Edit";
import DeleteIcon from "@mui/icons-material/Delete";
import { useTranslation } from "react-i18next";

// 1. Recibe 'auth' y 'items' como props
export default function Index({ auth, items }) {
    const { t } = useTranslation(["translation"]);
    const { delete: destroy } = useForm();
    const handleDelete = (id) => {
        if (confirm("¿Estás seguro que deseas eliminar este ítem?")) {
            destroy(route("admin.item.destroy", id));
        }
    };
    return (
        // 2. Pasa 'auth' al AdminLayout
        <AdminLayout auth={auth}>
            <Head title="Ítems de Inventario" />
            <div>
                <h1 className="text-2xl font-bold mb-4">Listado de Ítems</h1>

                <Link href={route('admin.item.create')} className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Crear Ítem
                </Link>

                <table className="table-auto w-full border mt-4">
                    <thead className="bg-gray-100">
                        <tr>
                            <th className="border px-4 py-2 text-left">ID</th>
                            <th className="border px-4 py-2 text-left">Nombre</th>
                            <th className="border px-4 py-2 text-left">Descripción</th>
                            <th className="border px-4 py-2 text-left">Categoría</th>
                            <th className="border px-4 py-2 text-left">Estado Actual</th>
                            <th className="border px-4 py-2 text-left">Ubicación Actual</th>
                            <th className="border px-4 py-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {/* 3. Itera sobre 'items.data', que es el array real */}
                        {items.data.map((item) => (
                            <tr key={item.id} className="border-t hover:bg-gray-50">
                                <td className="border px-4 py-2">{item.id}</td>
                                <td className="border px-4 py-2">{item.name}</td>
                                <td className="border px-4 py-2">{item.description}</td>
                                <td className="border px-4 py-2">{item.category?.name}</td>
                                <td className="border px-4 py-2">{item.current_status?.name}</td>
                                <td className="border px-4 py-2">{item.current_location?.name}</td>
                                <td className="border px-4 py-2 text-center">
                                    <div className="flex justify-center gap-2">
                                        <Link href={route('admin.item.edit', item.id)}>
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
                                            onClick={() => handleDelete(item.id)}
                                            className="ml-2"
                                        >
                                            {t("Delete")}
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>

                <div className="mt-4 flex justify-between items-center">
                    {/* Muestra información sobre los resultados */}
                    <div className="text-sm text-gray-500">
                        Mostrando {items.from} a {items.to} de {items.total} resultados
                    </div>

                    {/* Renderiza los enlaces de paginación */}
                    <div className="flex space-x-2">
                        {items.links.map((link, index) => (
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