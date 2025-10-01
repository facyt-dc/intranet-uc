import React from 'react';
import { Link, Head, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Button from "@mui/material/Button";
import EditIcon from "@mui/icons-material/Edit";
import DeleteIcon from "@mui/icons-material/Delete";
import { useTranslation } from "react-i18next";

export default function Index({ auth, categories }) {
	const { t } = useTranslation(["translation"]);
	const { delete: destroy } = useForm();
	const handleDelete = (id) => {
		if (confirm("¿Estás seguro que deseas eliminar esta categoría?")) {
			destroy(route("admin.item-category.destroy", id));
		}
	};
	return (
		<AdminLayout auth={auth}>
			<Head title="Categorías de Ítems" />
			<div>
				<h1 className="text-2xl font-bold mb-4">Categorías de Ítems</h1>

				<Link href={route('admin.item-category.create')} className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
					Crear Categoría
				</Link>

				<table className="table-auto w-full mt-4 border">
					<thead>
						<tr>
							<th className="border px-4 py-2 text-left">ID</th>
							<th className="border px-4 py-2 text-left">Nombre</th>
							<th className="border px-4 py-2 text-left">Descripción</th>
							<th className="border px-4 py-2 text-center">Acciones</th>
						</tr>
					</thead>
					<tbody>
						{categories.data.map((category) => (
							<tr key={category.id}>
								<td className="border px-4 py-2">{category.id}</td>
								<td className="border px-4 py-2">{category.name}</td>
								<td className="border px-4 py-2">{category.description}</td>
								<td className="border px-4 py-2 text-center">
									<div className="flex justify-center gap-2">
										<Link href={route('admin.item-category.edit', category.id)}>
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
											onClick={() => handleDelete(category.id)}
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
					<div className="text-sm text-gray-500">
						Mostrando {categories.from} a {categories.to} de {categories.total} resultados
					</div>
					<div className="flex space-x-2">
						{categories.links.map((link, index) => (
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
