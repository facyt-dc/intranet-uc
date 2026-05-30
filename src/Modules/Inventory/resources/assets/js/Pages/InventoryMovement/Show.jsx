// resources/js/Pages/Inventory/InventoryMovement/Show.jsx
import React from "react";
import { Link, Head } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import Button from "@mui/material/Button";
import IconButton from "@mui/material/IconButton";
import Tooltip from "@mui/material/Tooltip";
import ArrowCircleLeftRoundedIcon from "@mui/icons-material/ArrowCircleLeftRounded";
import FileCopyIcon from "@mui/icons-material/FileCopyOutlined";
import VisibilityIcon from "@mui/icons-material/Visibility";
import { useState } from "react";

export default function Show({ auth, movement }) {

  const [copied, setCopied] = useState(false);

  const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    try {
      const options = {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      };
      return new Date(dateString).toLocaleString("es-VE", options);
    } catch {
      return dateString;
    }
  };

  const handleCopyDetails = async () => {
    try {
      const payload = movement?.details ? JSON.stringify(movement.details, null, 2) : "{}";
      await navigator.clipboard.writeText(payload);
      setCopied(true);
      setTimeout(() => setCopied(false), 1800);
    } catch (e) {

      console.error("copy failed", e);
    }
  };

  if (!movement) {
    return (
      <AdminLayout auth={auth}>
        <Head title="Movimiento - No disponible" />
        <div className="p-6">
          <h2 className="text-xl font-semibold">Movimiento no encontrado</h2>
          <p className="mt-3 text-sm text-gray-500">El movimiento solicitado no está disponible.</p>
          <div className="mt-4">
            <Link href={route("admin.item-inventory-movement.index")}>
              <Button variant="contained" startIcon={<ArrowCircleLeftRoundedIcon />}>Volver</Button>
            </Link>
          </div>
        </div>
      </AdminLayout>
    );
  }

  return (
    <AdminLayout auth={auth}>
      <Head title={`Movimiento #${movement.id}`} />

      <div className="p-6">
        <div className="flex justify-between items-start mb-6 gap-4">
          <div>
            <h1 className="text-2xl font-bold">Movimiento #{movement.id}</h1>
            <p className="text-sm text-gray-500 mt-1">
              {movement.movement_type?.name || "Tipo no especificado"} — {formatDate(movement.movement_date)}
            </p>
          </div>

          <div className="flex items-center gap-2">
            <Link href={route("admin.item-inventory-movement.index")}>
              <Tooltip title="Volver al listado">
                <IconButton size="large">
                  <ArrowCircleLeftRoundedIcon fontSize="inherit" />
                </IconButton>
              </Tooltip>
            </Link>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* --- Main info (2/3) --- */}
          <div className="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm">
            <h2 className="text-lg font-medium mb-4">Detalles del Movimiento</h2>

            <dl className="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
              <div>
                <dt className="text-xs text-gray-500">ID</dt>
                <dd className="mt-1 text-sm font-medium">{movement.id}</dd>
              </div>

              <div>
                <dt className="text-xs text-gray-500">Ítem</dt>
                <dd className="mt-1 text-sm font-medium">
                  {movement.item ? (
                    <Link href={route("admin.item.edit", movement.item.id)} className="text-blue-600 hover:underline">
                      {movement.item.name}
                    </Link>
                  ) : (
                    "N/A"
                  )}
                </dd>
              </div>

              <div>
                <dt className="text-xs text-gray-500">Tipo de movimiento</dt>
                <dd className="mt-1 text-sm">{movement.movement_type?.name || "N/A"}</dd>
              </div>

              <div>
                <dt className="text-xs text-gray-500">Usuario responsable</dt>
                <dd className="mt-1 text-sm">{movement.user?.name || "N/A"}</dd>
              </div>

              <div>
                <dt className="text-xs text-gray-500">Fecha del movimiento</dt>
                <dd className="mt-1 text-sm">{formatDate(movement.movement_date)}</dd>
              </div>

              <div>
                <dt className="text-xs text-gray-500">Creado</dt>
                <dd className="mt-1 text-sm">{formatDate(movement.created_at)}</dd>
              </div>

              <div className="sm:col-span-2 mt-2">
                <dt className="text-xs text-gray-500">Descripción</dt>
                <dd className="mt-1 text-sm whitespace-pre-line">{movement.description || "-"}</dd>
              </div>
            </dl>
          </div>

          {/* --- JSON details (1/3) --- */}
          <div className="bg-white p-6 rounded-lg shadow-sm">
            <div className="flex justify-between items-center mb-3">
              <h3 className="text-md font-medium">Detalles (JSON)</h3>
              <div className="flex items-center gap-2">
                <Tooltip title={copied ? "Copiado" : "Copiar JSON"}>
                  <IconButton size="small" onClick={handleCopyDetails}>
                    <FileCopyIcon fontSize="small" />
                  </IconButton>
                </Tooltip>
                <Tooltip title="Ver raw">
                  <a
                    href="#raw"
                    onClick={(e) => {
                      e.preventDefault();
                      // simple scroll to pre
                      document.getElementById("movement-details-pre")?.scrollIntoView({ behavior: "smooth" });
                    }}
                  >
                    <IconButton size="small">
                      <VisibilityIcon fontSize="small" />
                    </IconButton>
                  </a>
                </Tooltip>
              </div>
            </div>

            {movement.details && Object.keys(movement.details).length > 0 ? (
              <pre
                id="movement-details-pre"
                className="max-h-[320px] overflow-auto text-sm bg-gray-100 rounded p-3 whitespace-pre-wrap break-words"
              >
                {JSON.stringify(movement.details, null, 2)}
              </pre>
            ) : (
              <p className="text-sm text-gray-500">No hay detalles registrados para este movimiento.</p>
            )}
          </div>
        </div>

        {/* Botón volver al final */}
        <div className="mt-6">
          <Link href={route("admin.item-inventory-movement.index")}>
            <Button variant="contained">Volver al listado</Button>
          </Link>
        </div>
      </div>
    </AdminLayout>
  );
}
