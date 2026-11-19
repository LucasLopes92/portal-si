"""Monta o ZIP de entrega somente com arquivos selecionados no indice do Git."""

from pathlib import Path
import hashlib
import subprocess
import zipfile


ROOT = Path(__file__).resolve().parents[1]
DESTINO = ROOT / "entrega" / "DSL_Projeto_Portal-SI.zip"


def main():
    arquivos = subprocess.check_output(
        ["git", "ls-files", "--cached", "-z"], cwd=ROOT
    ).decode("utf-8").split("\0")
    arquivos = sorted(set(p for p in arquivos if p and not p.startswith("entrega/")))
    if any(".local." in p or Path(p).name.startswith(".env") for p in arquivos):
        raise SystemExit("Arquivo privado selecionado no indice; revise antes de empacotar.")

    DESTINO.parent.mkdir(exist_ok=True)
    temporario = DESTINO.with_suffix(".tmp.zip")
    manifesto = []
    with zipfile.ZipFile(temporario, "w", zipfile.ZIP_DEFLATED, compresslevel=9) as zip_file:
        for relativo in arquivos:
            conteudo = (ROOT / relativo).read_bytes()
            manifesto.append(f"{hashlib.sha256(conteudo).hexdigest()}  {relativo}")
            zip_file.writestr("portal-si/" + relativo, conteudo)
        zip_file.writestr("portal-si/MANIFESTO_SHA256.txt", "\n".join(manifesto) + "\n")

    with zipfile.ZipFile(temporario) as zip_file:
        if zip_file.testzip() is not None:
            raise SystemExit("Falha de integridade no ZIP.")
    temporario.replace(DESTINO)
    print(f"{DESTINO.name}: {len(arquivos)} arquivos + manifesto SHA-256")
    print(f"SHA-256: {hashlib.sha256(DESTINO.read_bytes()).hexdigest()}")


if __name__ == "__main__":
    main()
