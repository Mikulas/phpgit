package pro.dite;

import org.eclipse.jgit.diff.DiffEntry;
import org.eclipse.jgit.diff.DiffFormatter;
import org.eclipse.jgit.diff.EditList;
import org.eclipse.jgit.diff.RawText;

import java.io.IOException;
import java.io.OutputStream;
import java.util.ArrayList;
import java.util.List;

public class Differ extends DiffFormatter
{
    public ArrayList<FileEdits> fileEdits = new ArrayList<FileEdits>();

    public Differ(OutputStream out)
    {
        super(out);
    }

    @Override
    public void format(EditList edits, RawText a, RawText b) throws IOException
    {
        fileEdits.add(new FileEdits(edits, a, b));
        super.format(edits, a, b);
    }

    public ArrayList<FileEdits> getEdits(DiffEntry diff) throws IOException
    {
        format(diff);
        return fileEdits;
    }

    public class FileEdits
    {
        public EditList edits;
        public RawText a;
        public RawText b;

        private FileEdits(EditList edits, RawText a, RawText b)
        {
            this.edits = edits;
            this.a = a;
            this.b = b;
        }
    }
}
