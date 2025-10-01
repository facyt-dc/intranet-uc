import React from "react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, useForm } from "@inertiajs/react";
import Button from "@mui/material/Button";
import AddRoundedIcon from "@mui/icons-material/AddRounded";
import EditIcon from "@mui/icons-material/Edit";
import DeleteIcon from "@mui/icons-material/Delete";
import Alert from "@/Components/Alert";
import { useTranslation } from "react-i18next";

export default function Index({ auth, movementTypes, flash }) {
	const { t } = useTranslation(["translation", "common"]);
	const { delete: destroy } = useForm();

	const handleDelete = (id) => {
		if (!confirm("¿Seguro que deseas eliminar este registro?")) return;
		destroy(route("admin.item-movement-type.destroy", id));
	};

	return (
		<AdminLayout auth={auth}>
			<Head title="Tipos de Movimiento" />
			<div>
				<h1 className="text-2xl font-bold mb-4">Tipos de Movimiento</h1>

				<Link href={route("admin.item-movement-type.create")} className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
					Crear Movimiento
				</Link>

				<table className="table-auto w-full border mt-4">
					<thead className="bg-gray-100">
						<tr>
							<th className="border px-4 py-2 text-left">ID</th>
							<th className="border px-4 py-2 text-left">Nombre</th>
							<th className="border px-4 py-2 text-left">Descripción</th>
							<th className="border px-4 py-2 text-center">Acciones</th>
						</tr>
					</thead>
					<tbody>
						{movementTypes.data.length === 0 ? (
							<tr>
								<td colSpan="4" className="p-4 text-center text-gray-500">
									No hay tipos de movimiento para mostrar.
								</td>
							</tr>
						) : (
							movementTypes.data.map((type) => (
								<tr key={type.id} className="border-t hover:bg-gray-50">
									<td className="border px-4 py-2">{type.id}</td>
									<td className="border px-4 py-2">{type.name}</td>
									<td className="border px-4 py-2">{type.description}</td>
									<td className="border px-4 py-2 text-center">
										<div className="flex justify-center gap-2">
											<Link href={route("admin.item-movement-type.edit", type.id)}>
												<Button variant="contained" size="small" startIcon={<EditIcon />}>
													{t("Edit")}
												</Button>
											</Link>

											<Button
												variant="outlined"
												color="error"
												size="small"
												startIcon={<DeleteIcon />}
												onClick={() => handleDelete(type.id)}
												className="ml-2"
											>
												{t("Delete")}
											</Button>
										</div>
									</td>
								</tr>
							))
						)}
					</tbody>
				</table>

				<div className="mt-4 flex justify-between items-center">
					<div className="text-sm text-gray-500">
						Mostrando {movementTypes.from} a {movementTypes.to} de {movementTypes.total} resultados
					</div>
					<div className="flex space-x-2">
						{movementTypes.links.map((link, index) => (
							<Link
								key={index}
								href={link.url}
								dangerouslySetInnerHTML={{ __html: link.label }}
								className={`px-4 py-2 border rounded ${!link.url ? "bg-gray-200 text-gray-400 cursor-not-allowed" : ""
									} ${link.active ? "bg-blue-600 text-white" : "bg-white"}`}
							/>
						))}
					</div>
				</div>
			</div>
		</AdminLayout>
	);
}
