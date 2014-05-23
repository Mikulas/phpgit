package pro.dite;

import org.eclipse.jgit.api.errors.GitAPIException;
import org.eclipse.jgit.diff.*;
import org.eclipse.jgit.revwalk.RevCommit;

import java.io.File;
import java.io.IOException;
import java.util.HashSet;
import java.util.Hashtable;

public class Main
{

    public static void main(String[] args) throws IOException, GitAPIException
    {
        // TODO do not require being in root
        String gitDir = System.getProperty("user.dir") + "/.git";

        final Hashtable<String, HashSet<String>> index = new Hashtable<String, HashSet<String>>();
        File repoDir = new File(gitDir);
        HeadWalker walker = new HeadWalker(repoDir)
        {
            @Override
            public void processFileDiff(RevCommit commit, EditList edits, PhpFile a, PhpFile b)
            {
                for (Edit edit : edits)
                {
                    if (edit.getType() == Edit.Type.REPLACE || edit.getType() == Edit.Type.DELETE)
                    {
                        processEdit(index, a, edit.getBeginA(), edit.getEndA(), commit);
                    }
                    if (edit.getType() == Edit.Type.REPLACE || edit.getType() == Edit.Type.INSERT)
                    {
                        processEdit(index, b, edit.getBeginB(), edit.getEndB(), commit);
                    }
                }
            }
        };

        walker.walk();

        if (args.length >= 1)
        {
            String def = args[0];
            System.out.println("Authors of: " + def);
            if (!index.containsKey(def))
            {
                System.out.println("definition not found");
                return;
            }
            HashSet<String> authors = index.get(def);
            for (String author : authors)
            {
                System.out.println("\t" + author);
            }
        }
    }

    private static void processEdit(Hashtable<String, HashSet<String>> index, PhpFile php, int begin, int end, RevCommit commit)
    {
        for (int i = begin; i < end; ++i)
        {
            PhpFile.Line line = php.lines.get(i);
            if (line.isFunction())
            {
                String def = line.toStringFunction();
                if (!index.containsKey(def))
                {
                    index.put(def, new HashSet<String>());
                }
                index.get(def).add(commit.getAuthorIdent().getEmailAddress());
            }

            String def = line.toString();
            if (!index.containsKey(def))
            {
                index.put(def, new HashSet<String>());
            }
            index.get(def).add(commit.getAuthorIdent().getEmailAddress());
        }
    }

}
